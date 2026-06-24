<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\MerchandiseVariant;
use App\Models\TicketCategory;
use App\Services\User\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService
    ) {}

    /**
     * Tampilkan halaman checkout.
     */
    public function index(Request $request)
    {
        $ticketSelections = $request->query('tickets', []);
        $merchSelections = $request->query('merchandise', []);

        // Filter empty or zero selections
        $ticketSelections = array_filter($ticketSelections, fn ($qty) => (int) $qty > 0);
        $merchSelections = array_filter($merchSelections, fn ($qty) => (int) $qty > 0);

        if (empty($ticketSelections)) {
            return redirect()->route('events.index')->with('error', 'Silakan pilih tiket terlebih dahulu sebelum melakukan checkout.');
        }

        // Fetch ticket categories
        $categories = TicketCategory::with('event')
            ->whereIn('id', array_keys($ticketSelections))
            ->get();

        if ($categories->isEmpty()) {
            return redirect()->route('events.index')->with('error', 'Tiket yang Anda pilih tidak valid.');
        }

        // Identify event
        $event = $categories->first()->event;

        // Verify all ticket categories belong to the same event
        foreach ($categories as $cat) {
            if ($cat->event_id !== $event->id) {
                return redirect()->route('events.index')->with('error', 'Semua tiket dalam satu transaksi harus berasal dari acara yang sama.');
            }
        }

        // Apply Server-Side Cancellation Guard (Task 4.6)
        if ($event->status !== EventStatus::Published) {
            return redirect()->route('events.show', $event->slug)
                ->with('error', 'Tiket tidak dapat dibeli karena penjualan untuk acara ini telah ditutup.');
        }

        // Map selections with quantity and totals
        $tikets = $categories->map(function ($cat) use ($ticketSelections) {
            $qty = (int) $ticketSelections[$cat->id];

            return (object) [
                'id' => $cat->id,
                'nama' => $cat->name,
                'qty' => $qty,
                'harga' => $cat->price,
                'max_per_user' => $cat->max_per_user,
            ];
        });

        // Fetch merchandise variants
        $merchandises = collect();
        if (! empty($merchSelections)) {
            $variants = MerchandiseVariant::with('item')
                ->whereIn('id', array_keys($merchSelections))
                ->get();

            $merchandises = $variants->map(function ($v) use ($merchSelections) {
                $qty = (int) $merchSelections[$v->id];

                return (object) [
                    'id' => $v->id,
                    'nama' => $v->item->name,
                    'qty' => $qty,
                    'harga' => $v->final_price,
                    'varian' => $v->variant_value,
                ];
            });
        }

        $subtotal = $tikets->sum(fn ($t) => $t->harga * $t->qty)
            + $merchandises->sum(fn ($m) => $m->harga * $m->qty);
        $biaya_layanan = 15000;
        $pajak = (int) round($subtotal * 0.1);
        $total = $subtotal + $biaya_layanan + $pajak;

        return view('user.checkout', compact(
            'event',
            'tikets',
            'merchandises',
            'subtotal',
            'biaya_layanan',
            'pajak',
            'total'
        ));
    }

    /**
     * Proses data checkout.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $request->merge([
            'nama_lengkap' => $user->name,
            'email' => $user->email,
        ]);

        $validator = Validator::make($request->all(), [
            'event_id' => ['required', 'exists:events,id'],
            'nama_lengkap' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'no_telepon' => ['required', 'string', 'regex:/^[0-9\+\-\s]{8,20}$/'],
            'tickets' => ['required', 'array'],
            'merchandise' => ['nullable', 'array'],
            'holder_names' => ['required', 'array'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.min' => 'Nama minimal 3 karakter.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'no_telepon.required' => 'Nomor telepon wajib diisi.',
            'no_telepon.regex' => 'Format nomor telepon tidak valid.',
            'holder_names.required' => 'Nama pemegang tiket wajib diisi.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $event = Event::findOrFail($request->event_id);
        $user = Auth::user();

        // Enforce Server-Side Cancellation Guard (Task 4.6)
        if ($event->status !== EventStatus::Published) {
            return redirect()->route('events.show', $event->slug)
                ->with('error', 'Tiket tidak dapat dibeli karena penjualan untuk acara ini telah ditutup.');
        }

        $ticketSelections = array_filter($request->tickets, fn ($qty) => (int) $qty > 0);
        $merchSelections = array_filter($request->merchandise ?? [], fn ($qty) => (int) $qty > 0);

        try {
            $order = $this->checkoutService->createOrder(
                $user,
                $event,
                $ticketSelections,
                $merchSelections,
                $request->holder_names
            );

            return redirect()->route('pesanan.show', $order->id)
                ->with('success', 'Pesanan berhasil dibuat! Silakan lakukan pembayaran.');

        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
