<?php

namespace App\Http\Controllers\Organizer;

use App\Enums\EventStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Organizer\StoreEventRequest;
use App\Http\Requests\Organizer\UpdateEventRequest;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\MerchandiseItem;
use App\Models\MerchandiseVariant;
use App\Models\Order;
use App\Models\TicketCategory;
use App\Services\Organizer\CancellationService;
use App\Services\Organizer\EventService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        protected EventService $eventService
    ) {}

    public function index(Request $request): View
    {
        $organizerId = auth()->id();
        $events = $this->eventService->getPaginatedEventsForOrganizer($organizerId, $request->all());

        $eventIds = Event::where('organizer_id', $organizerId)->pluck('id');

        $ticketsSold = (int) TicketCategory::whereIn('event_id', $eventIds)->sum('sold_count');

        $totalRevenue = (int) Order::whereIn('event_id', $eventIds)->where('status', OrderStatus::Paid)->sum('total_amount');

        $upcomingEvents = Event::where('organizer_id', $organizerId)
            ->where('status', EventStatus::Published)
            ->where('event_date', '>=', now()->toDateString())
            ->count();

        $categories = EventCategory::orderBy('name')->pluck('name', 'id');
        $statuses = collect(EventStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]);

        return view('organizer.events.index', compact('events', 'ticketsSold', 'totalRevenue', 'upcomingEvents', 'categories', 'statuses'));
    }

    public function create(): View
    {
        $categories = EventCategory::orderBy('name')->get();

        return view('organizer.events.create', compact('categories'));
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $bannerPath = $this->handleBannerUpload($request);

        DB::transaction(function () use ($request, $bannerPath): void {
            $event = Event::create([
                'id' => Str::uuid(),
                'organizer_id' => $request->user()->id,
                'category_id' => $request->string('category_id'),
                'name' => $request->string('name'),
                'slug' => Str::slug($request->string('name')).'-'.Str::random(5),
                'description' => $request->string('description'),
                'venue_name' => $request->string('venue_name'),
                'address' => $request->string('address'),
                'city' => $request->string('city'),
                'event_date' => $request->date('event_date'),
                'start_time' => $request->string('start_time'),
                'end_time' => $request->string('end_time'),
                'status' => $request->input('status'),
                'banner_image' => $bannerPath,
                'is_featured' => false,
            ]);

            $this->syncTicketCategories($event, $request->validated('tickets'));

            if ($request->has('merchandise') && is_array($request->validated('merchandise'))) {
                $this->syncMerchandiseItems($event, $request->validated('merchandise'), $request);
            }
        });

        return redirect()->route('organizer.events.index')->with('status', 'Acara beserta tiket berhasil dibuat.');
    }

    public function show(Request $request, string $id): View
    {
        $event = Event::with(['ticketCategories', 'merchandiseItems.variants'])
            ->where('organizer_id', $request->user()->id)
            ->findOrFail($id);

        $filter = $request->query('filter', '30'); // '7', '30', 'all'

        $paidOrdersQuery = Order::query()
            ->where('event_id', $event->id)
            ->where('status', OrderStatus::Paid);

        if ($filter !== 'all') {
            $days = (int) $filter;
            $paidOrdersQuery->where('paid_at', '>=', now()->subDays($days)->startOfDay());
        } else {
            $days = 30; // default for chart labels if all time, or we can use diffInDays from first order
            $firstOrder = (clone $paidOrdersQuery)->oldest('paid_at')->first();
            if ($firstOrder && $firstOrder->paid_at) {
                $days = max(7, (int) now()->diffInDays($firstOrder->paid_at));
            }
        }

        // Aggregate Stats
        $totalRevenue = (clone $paidOrdersQuery)->sum('total_amount');

        $ticketSold = 0;
        $merchSold = 0;

        // Transaction Activity (Paginated)
        $transactions = (clone $paidOrdersQuery)
            ->with(['user', 'tickets.ticketCategory', 'merchandise.merchandiseItem', 'merchandise.merchandiseVariant'])
            ->latest('paid_at')
            ->paginate(10)
            ->withQueryString();

        // Calculate dynamic ticket & merch sold based on filtered orders
        // Because $event->ticketCategories->sum('sold_count') is all-time, we need to calculate it for the date range
        // If filter is all, we can just use the models. Otherwise, query order relationships.
        if ($filter === 'all') {
            $ticketSold = $event->ticketCategories->sum('sold_count');
            // For merch sold, we'd need to query if there's a sold_count on merch.
            // Wait, there is no sold_count on merchandise_items in DB. We query OrderMerchandise anyway.
        }

        $filteredOrdersIds = (clone $paidOrdersQuery)->pluck('id');

        $filteredTicketSold = DB::table('order_tickets')
            ->whereIn('order_id', $filteredOrdersIds)
            ->count();

        $filteredMerchSold = DB::table('order_merchandise')
            ->whereIn('order_id', $filteredOrdersIds)
            ->sum('quantity');

        $ticketSold = $filteredTicketSold;
        $merchSold = $filteredMerchSold;

        $stats = [
            'total_revenue' => $totalRevenue,
            'ticket_sold' => $ticketSold,
            'merch_sold' => $merchSold,
        ];

        // Charts Data
        $chartDays = collect(range($days, 0))->map(fn (int $day): string => now()->subDays($day)->format('Y-m-d'));

        $dailyTransactions = (clone $paidOrdersQuery)
            ->selectRaw('DATE(paid_at) as date, SUM(total_amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $analytics = [
            'labels' => $chartDays->map(fn (string $date): string => Carbon::parse($date)->translatedFormat('d M'))->toArray(),
            'revenue' => $chartDays->map(fn (string $date): int => $dailyTransactions->has($date) ? (int) $dailyTransactions[$date]->total : 0)->toArray(),
            'volume' => $chartDays->map(fn (string $date): int => $dailyTransactions->has($date) ? (int) $dailyTransactions[$date]->count : 0)->toArray(),
        ];

        // Distribution Data
        $ticketDistributionData = DB::table('order_tickets')
            ->join('ticket_categories', 'order_tickets.ticket_category_id', '=', 'ticket_categories.id')
            ->whereIn('order_tickets.order_id', $filteredOrdersIds)
            ->selectRaw('ticket_categories.name as label, COUNT(order_tickets.id) as count')
            ->groupBy('ticket_categories.name')
            ->orderByDesc('count')
            ->get();

        $merchDistributionData = DB::table('order_merchandise')
            ->join('merchandise_variants', 'order_merchandise.merchandise_variant_id', '=', 'merchandise_variants.id')
            ->join('merchandise_items', 'merchandise_variants.merchandise_item_id', '=', 'merchandise_items.id')
            ->whereIn('order_merchandise.order_id', $filteredOrdersIds)
            ->selectRaw('merchandise_items.name as label, SUM(order_merchandise.quantity) as count')
            ->groupBy('merchandise_items.name')
            ->orderByDesc('count')
            ->get();

        $tones = ['bg-violet-600', 'bg-fuchsia-500', 'bg-sky-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500'];

        $ticketTotal = max($filteredTicketSold, 1);
        $ticketDistribution = $ticketDistributionData->map(fn ($item, $index) => [
            'label' => $item->label,
            'count' => $item->count,
            'percentage' => round(($item->count / $ticketTotal) * 100),
            'color' => $tones[$index % count($tones)],
        ]);

        $merchTotal = max($filteredMerchSold, 1);
        $merchDistribution = $merchDistributionData->map(fn ($item, $index) => [
            'label' => $item->label,
            'count' => $item->count,
            'percentage' => round(($item->count / $merchTotal) * 100),
            'color' => $tones[$index % count($tones)],
        ]);

        // Variant Sales Breakdown
        $merchVariantsSold = DB::table('order_merchandise')
            ->join('merchandise_variants', 'order_merchandise.merchandise_variant_id', '=', 'merchandise_variants.id')
            ->join('merchandise_items', 'merchandise_variants.merchandise_item_id', '=', 'merchandise_items.id')
            ->whereIn('order_merchandise.order_id', $filteredOrdersIds)
            ->selectRaw('merchandise_items.name as item_name, merchandise_variants.variant_group, merchandise_variants.variant_value, SUM(order_merchandise.quantity) as total_sold, SUM(order_merchandise.quantity * order_merchandise.unit_price) as total_revenue')
            ->groupBy('merchandise_items.name', 'merchandise_variants.variant_group', 'merchandise_variants.variant_value')
            ->orderBy('item_name')
            ->get();

        return view('organizer.events.show', compact(
            'event', 'filter', 'stats', 'transactions', 'analytics',
            'ticketDistribution', 'merchDistribution', 'merchVariantsSold'
        ));
    }

    public function edit(string $id): View|RedirectResponse
    {
        $event = Event::with('category', 'ticketCategories', 'merchandiseItems')
            ->where('organizer_id', auth()->id())
            ->findOrFail($id);

        if ($event->status === EventStatus::Completed) {
            return redirect()->route('organizer.events.index')
                ->withErrors(['error' => 'Acara yang telah selesai tidak dapat diubah kembali.']);
        }

        $categories = EventCategory::orderBy('name')->get();
        $pendingCancellation = $event->cancellationRequests()->where('status', 'pending')->latest()->first();

        return view('organizer.events.edit', compact('event', 'categories', 'pendingCancellation'));
    }

    public function update(UpdateEventRequest $request, string $id): RedirectResponse
    {
        $event = Event::query()
            ->with('ticketCategories')
            ->where('organizer_id', $request->user()->id)
            ->findOrFail($id);

        if ($event->status === EventStatus::Completed) {
            return redirect()->route('organizer.events.index')
                ->withErrors(['error' => 'Acara yang telah selesai tidak dapat diubah kembali.']);
        }

        $isLocked = $event->status === EventStatus::Published && $event->hasSales();

        $bannerPath = $this->handleBannerUpload($request, $event->banner_image);

        DB::transaction(function () use ($event, $request, $bannerPath, $isLocked): void {
            $newStatus = $request->input('status');
            $rejectionMessage = $newStatus === 'awaiting_approval' ? null : $event->rejection_message;

            $event->update([
                'category_id' => $request->string('category_id'),
                'name' => $request->string('name'),
                'description' => $request->string('description'),
                'venue_name' => $isLocked ? $event->venue_name : $request->string('venue_name'),
                'address' => $isLocked ? $event->address : $request->string('address'),
                'city' => $isLocked ? $event->city : $request->string('city'),
                'event_date' => $isLocked ? $event->event_date : $request->date('event_date'),
                'start_time' => $isLocked ? $event->start_time : $request->string('start_time'),
                'end_time' => $isLocked ? $event->end_time : $request->string('end_time'),
                'status' => $newStatus,
                'banner_image' => $bannerPath,
                'rejection_message' => $rejectionMessage,
            ]);

            $this->syncTicketCategories($event, $request->validated('tickets'), $isLocked);

            if ($request->has('merchandise') && is_array($request->validated('merchandise'))) {
                $this->syncMerchandiseItems($event, $request->validated('merchandise'), $request);
            } else {
                foreach ($event->merchandiseItems as $item) {
                    if ($item->image) {
                        Storage::disk('public')->delete($item->image);
                    }
                    $item->delete();
                }
            }
        });

        return redirect()->route('organizer.events.index')->with('status', 'Perubahan acara berhasil disimpan.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $event = Event::query()
            ->where('organizer_id', auth()->id())
            ->findOrFail($id);

        if (auth()->user()->cannot('delete', $event)) {
            return redirect()->route('organizer.events.index')
                ->withErrors(['error' => 'Acara tidak dapat dihapus karena telah diterbitkan dan memiliki tiket terjual yang belum selesai.']);
        }

        $event->delete();

        return redirect()->route('organizer.events.index')->with('status', 'Acara berhasil dihapus dari daftar aktif.');
    }

    /**
     * @param  array<int, array{name: string, price: int|string, quota: int|string}>  $tickets
     */
    private function syncTicketCategories(Event $event, array $tickets, bool $isLocked = false): void
    {
        $existingTicketIds = collect($tickets)
            ->pluck('id')
            ->filter(fn ($id) => $id && ! str_starts_with((string) $id, 'ticket-'))
            ->toArray();

        // Prevent deleting tickets that have sales.
        $event->ticketCategories()
            ->whereNotIn('id', $existingTicketIds)
            ->where('sold_count', 0)
            ->delete();

        foreach ($tickets as $ticket) {
            $isExisting = isset($ticket['id']) && ! str_starts_with((string) $ticket['id'], 'ticket-');

            if ($isExisting) {
                $category = TicketCategory::find($ticket['id']);
                if ($category) {
                    $category->update([
                        'name' => $ticket['name'],
                        'price' => $isLocked ? $category->price : ($ticket['price'] ?? $category->price),
                        'quota' => $ticket['quota'],
                        'max_per_user' => $ticket['max_per_user'] ?? null,
                    ]);
                }
            } else {
                TicketCategory::create([
                    'id' => Str::uuid(),
                    'event_id' => $event->id,
                    'name' => $ticket['name'],
                    'price' => $ticket['price'],
                    'quota' => $ticket['quota'],
                    'max_per_user' => $ticket['max_per_user'] ?? null,
                    'sale_start_at' => now(),
                    'sale_end_at' => Carbon::parse($event->event_date)->format('Y-m-d').' '.$event->end_time,
                    'is_active' => true,
                ]);
            }
        }
    }

    private function handleBannerUpload(Request $request, ?string $oldPath = null): ?string
    {
        if ($request->hasFile('banner_image')) {
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            return $request->file('banner_image')->store('events/banners', 'public');
        }

        return $oldPath;
    }

    private function syncMerchandiseItems(Event $event, array $merchandise, Request $request): void
    {
        $existingIds = $event->merchandiseItems()->pluck('id')->toArray();
        $submittedIds = collect($merchandise)->pluck('id')->filter()->toArray();

        // Delete removed items
        $toDelete = array_diff($existingIds, $submittedIds);
        if (! empty($toDelete)) {
            $itemsToDelete = $event->merchandiseItems()->whereIn('id', $toDelete)->get();
            foreach ($itemsToDelete as $item) {
                if ($item->image) {
                    Storage::disk('public')->delete($item->image);
                }
                $item->delete();
            }
        }

        foreach ($merchandise as $index => $itemData) {
            $itemId = $itemData['id'] ?? null;
            $imagePath = null;

            if ($request->hasFile("merchandise.{$index}.image")) {
                $imagePath = $request->file("merchandise.{$index}.image")->store('events/merchandise', 'public');
            }

            if ($itemId && in_array($itemId, $existingIds)) {
                $merchModel = $event->merchandiseItems()->find($itemId);

                if ($imagePath && $merchModel->image) {
                    Storage::disk('public')->delete($merchModel->image);
                }

                $merchModel->update([
                    'name' => $itemData['name'],
                    'base_price' => $itemData['base_price'],
                    'description' => $itemData['description'] ?? null,
                    'is_available' => isset($itemData['is_available']) ? (bool) $itemData['is_available'] : true,
                    'image' => $imagePath ?? $merchModel->image,
                ]);
            } else {
                $merchModel = MerchandiseItem::create([
                    'id' => Str::uuid(),
                    'event_id' => $event->id,
                    'name' => $itemData['name'],
                    'base_price' => $itemData['base_price'],
                    'description' => $itemData['description'] ?? null,
                    'is_available' => isset($itemData['is_available']) ? (bool) $itemData['is_available'] : true,
                    'image' => $imagePath,
                ]);
            }

            // Sync Variants
            $submittedVariants = $itemData['variants'] ?? [];
            $existingVariantIds = $merchModel->variants()->pluck('id')->toArray();
            $submittedVariantIds = collect($submittedVariants)->pluck('id')->filter()->toArray();

            $variantsToDelete = array_diff($existingVariantIds, $submittedVariantIds);
            if (! empty($variantsToDelete)) {
                $merchModel->variants()->whereIn('id', $variantsToDelete)->delete();
            }

            foreach ($submittedVariants as $variantData) {
                $variantId = $variantData['id'] ?? null;
                if ($variantId && in_array($variantId, $existingVariantIds)) {
                    $merchModel->variants()->find($variantId)->update([
                        'variant_group' => $variantData['group'],
                        'variant_value' => $variantData['value'],
                        'stock' => $variantData['stock'],
                        'price_adjustment' => $variantData['price_adjustment'],
                    ]);
                } else {
                    MerchandiseVariant::create([
                        'id' => Str::uuid(),
                        'merchandise_item_id' => $merchModel->id,
                        'variant_group' => $variantData['group'],
                        'variant_value' => $variantData['value'],
                        'stock' => $variantData['stock'],
                        'price_adjustment' => $variantData['price_adjustment'],
                    ]);
                }
            }
        }
    }

    public function cancel(Request $request, string $id, CancellationService $cancellationService): RedirectResponse
    {
        $event = Event::query()
            ->with('ticketCategories')
            ->where('organizer_id', $request->user()->id)
            ->findOrFail($id);

        if ($event->status === EventStatus::Completed) {
            return redirect()->route('organizer.events.index')
                ->withErrors(['error' => 'Acara yang telah selesai tidak dapat dibatalkan.']);
        }

        try {
            $cancellationService->cancelEvent($event);

            return redirect()->route('organizer.events.edit', $event)->with('status', 'Acara berhasil dibatalkan.');
        } catch (\Exception $e) {
            return redirect()->route('organizer.events.edit', $event)->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function requestCancellation(Request $request, string $id, CancellationService $cancellationService): RedirectResponse
    {
        $event = Event::query()
            ->with('ticketCategories')
            ->where('organizer_id', $request->user()->id)
            ->findOrFail($id);

        if ($event->status === EventStatus::Completed) {
            return redirect()->route('organizer.events.index')
                ->withErrors(['error' => 'Acara yang telah selesai tidak dapat dibatalkan.']);
        }

        $request->validate([
            'reason' => ['required', 'string', 'min:20', 'max:1000'],
        ]);

        try {
            $cancellationService->requestCancellation($event, $request->string('reason')->toString(), $request->user());

            return redirect()->route('organizer.events.edit', $event)->with('status', 'Pengajuan pembatalan acara berhasil dikirim. Menunggu persetujuan admin.');
        } catch (\Exception $e) {
            return redirect()->route('organizer.events.edit', $event)->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }
}
