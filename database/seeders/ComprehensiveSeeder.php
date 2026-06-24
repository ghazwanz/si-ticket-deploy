<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\CancellationRequest;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\MerchandiseItem;
use App\Models\MerchandiseVariant;
use App\Models\Order;
use App\Models\OrderMerchandise;
use App\Models\OrderTicket;
use App\Models\Payout;
use App\Models\TicketCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ComprehensiveSeeder extends Seeder
{
    protected static int $eventCounter = 0;

    public function run(): void
    {
        // Copy public/img/Event to storage/app/public/img/Event
        $sourcePath = public_path('img/Event');
        $destinationPath = storage_path('app/public/img/Event');

        if (File::exists($sourcePath)) {
            File::ensureDirectoryExists($destinationPath);
            File::copyDirectory($sourcePath, $destinationPath);
        }

        // 1. Ensure Categories exist
        $this->call(EventCategorySeeder::class);
        $categories = EventCategory::all();

        // 2. Create Admin
        User::firstOrCreate(
            ['email' => 'admin@joinfest.com'],
            [
                'name' => 'JoinFest Admin',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'is_active' => true,
            ]
        );

        // 3. Create regular customers
        $customers = User::factory()->count(30)->create(['role' => UserRole::User]);

        // 4. Create Organizers and their ecosystem (10 organizers * 5 statuses = 50 events)
        User::factory()->count(10)->create(['role' => UserRole::Organizer])->each(function ($organizer) use ($customers) {

            // Create Events for each Organizer
            $statuses = ['published', 'completed', 'awaiting_approval', 'awaiting_cancellation', 'cancelled'];

            foreach ($statuses as $status) {
                // Let the factory determine the category, banner image, venue, city, and dates dynamically
                $event = Event::factory()->create([
                    'organizer_id' => $organizer->id,
                    'status' => $status,
                ]);

                // Create Ticket Categories
                $ticketCategories = TicketCategory::factory()->count(3)->create([
                    'event_id' => $event->id,
                ]);

                // Loop assets for Merchandise
                $kaosTemplates = [
                    [
                        'name' => 'Kaos Mantra in Summer',
                        'image' => 'img/Event/KAOS MANTRA IN SUMMER.png',
                        'description' => 'Kaos Mantra in Summer dirancang sebagai merchandise utama yang merepresentasikan suasana festival musim panas yang fun, santai, dan penuh warna.',
                        'base_price' => 125000,
                    ],
                    [
                        'name' => 'Kaos Zephyer',
                        'image' => 'img/Event/KAOS ZEPHYER.png',
                        'description' => "Bahan Kaos:\n- Cotton Combed 24s / 30s\n- Tekstur lembut dan halus di kulit\n- Menyerap keringat dengan baik, cocok untuk event outdoor\n- Nyaman dipakai seharian oleh berbagai usia (terutama mahasiswa)\n- Jahitan: Rantai (overlock + coverstitch) agar kuat dan tahan lama\n- Warna dasar: Biru tua / navy atau putih (menyesuaikan konsep laut & angin)\n\nDesain Visual Kaos:\n- Mengadaptasi ilustrasi ombak, kapal, dan ornamen nusantara sebagai identitas utama event.\n- Menggunakan palet warna biru laut dengan aksen hangat untuk menciptakan kesan dinamis dan artistik.\n- Menampilkan nuansa petualangan dan kebebasan yang merepresentasikan semangat Zephyer Project.",
                        'base_price' => 135000,
                    ],
                    [
                        'name' => 'Kaos RW3 Fest',
                        'image' => 'img/Event/KAOS RW3 FEST.png',
                        'description' => "Bahan:\n- Menggunakan cotton combed 24s\n- Tekstur halus, ringan, dan nyaman dipakai seharian\n- Menyerap keringat dengan baik, cocok untuk aktivitas event indoor maupun outdoor\n\nDesain Visual:\n- Mengadaptasi identitas visual RW3 Fest dengan ornamen batik dan nuansa merah maroon yang kuat.\n- Logo “RW3 Fest” menjadi fokus utama dengan tipografi elegan dan mudah dikenali.\n- Detail ilustratif tradisional memberikan kesan budaya, eksklusif, dan berkelas tanpa terlihat berlebihan.",
                        'base_price' => 145000,
                    ],
                    [
                        'name' => 'Kaos Abrakadabra Konser',
                        'image' => 'img/Event/KAOS ABRAKADABRA KONSER.png',
                        'description' => "Bahan:\n- Menggunakan cotton combed 24s yang lembut, ringan, dan nyaman dipakai seharian.\n- Bahan menyerap keringat dengan baik sehingga cocok untuk aktivitas konser.\n- Jahitan rapi dan kuat, tidak mudah melar meskipun sering dicuci.\n\nDesain Visual:\n- Mengangkat tema sulap dan fantasi melalui ilustrasi topi penyihir, tongkat sihir, dan elemen magis.\n- Palet warna ungu dominan dipadukan dengan aksen emas untuk memberikan kesan magis, elegan, dan misterius.\n- Tipografi “Abrakadabra Konser” dibuat tegas dan artistik sebagai fokus utama desain.\n- Komposisi visual disederhanakan agar tetap menarik saat diaplikasikan pada media kaos tanpa elemen informasi acara.",
                        'base_price' => 130000,
                    ],
                ];

                $toteTemplates = [
                    [
                        'name' => 'Tote bag Mantra in Summer',
                        'image' => 'img/Event/TOTEBAG MANTRA IN SUMMER.png',
                        'description' => 'Tote bag Mantra in Summer dirancang sebagai merchandise fungsional yang tetap kuat secara visual dan identitas brand event.',
                        'base_price' => 65000,
                    ],
                    [
                        'name' => 'Tote bag Zephyer',
                        'image' => 'img/Event/TOTEBAG ZEPHYER.png',
                        'description' => "Bahan Tote Bag:\n- Canvas / Blacu Tebal\n- Kuat dan tidak mudah sobek\n- Ramah lingkungan (eco-friendly)\n- Bisa digunakan berulang kali\n- Handle: Jahitan reinforced agar kuat menahan beban\n\nDesain Visual Tote Bag:\n- Mengadaptasi ilustrasi dan warna dari desain kaos agar konsisten secara branding.\n- Menyederhanakan elemen visual kapal dan ombak agar tetap jelas pada media tas.\n- Nuansa eksplorasi dan estetika laut tetap terasa meskipun diaplikasikan pada tote bag.",
                        'base_price' => 75000,
                    ],
                    [
                        'name' => 'Tote Bag RW3 Fest',
                        'image' => 'img/Event/TOTEBAG RW3 FEST.png',
                        'description' => "Bahan:\n- Menggunakan canvas tebal (12–14 oz)\n- Kuat, tidak mudah robek, dan ramah lingkungan\n- Cocok digunakan untuk membawa buku, laptop, atau kebutuhan harian\n\nDesain Visual:\n- Mengadaptasi ilustrasi dan warna dari desain kaos agar konsisten secara branding.\n- Menampilkan logo RW3 Fest dan elemen arsitektur tradisional dengan komposisi seimbang.\n- Nuansa etnik-modern tetap terasa, membuat tote bag fungsional sekaligus estetik sebagai merchandise event.",
                        'base_price' => 85000,
                    ],
                ];

                $eventNum = self::$eventCounter++;
                $kaos = $kaosTemplates[$eventNum % count($kaosTemplates)];
                $tote = $toteTemplates[$eventNum % count($toteTemplates)];

                $merchItems = [
                    [
                        'name' => $kaos['name'].' - '.$event->name,
                        'image' => $kaos['image'],
                        'description' => $kaos['description'],
                        'base_price' => $kaos['base_price'],
                        'is_available' => true,
                    ],
                    [
                        'name' => $tote['name'].' - '.$event->name,
                        'image' => $tote['image'],
                        'description' => $tote['description'],
                        'base_price' => $tote['base_price'],
                        'is_available' => true,
                    ],
                ];

                foreach ($merchItems as $merchData) {
                    $item = MerchandiseItem::create(array_merge([
                        'id' => Str::uuid()->toString(),
                        'event_id' => $event->id,
                    ], $merchData));

                    // Variants for each item
                    $sizes = ['S', 'M', 'L', 'XL'];
                    foreach ($sizes as $size) {
                        MerchandiseVariant::factory()->create([
                            'merchandise_item_id' => $item->id,
                            'variant_group' => 'Size',
                            'variant_value' => $size,
                        ]);
                    }
                }

                // Create Orders if Event is published, completed, awaiting cancellation, or cancelled
                if (in_array($event->status->value, ['published', 'completed', 'awaiting_cancellation', 'cancelled'])) {
                    $orderCount = $event->status->value === 'completed' ? 12 : 6;

                    for ($i = 0; $i < $orderCount; $i++) {
                        $order = Order::factory()->create([
                            'user_id' => $customers->random()->id,
                            'event_id' => $event->id,
                            'status' => 'paid',
                            'paid_at' => now()->subDays(rand(1, 10)),
                        ]);

                        // Add tickets
                        $cat = $ticketCategories->random();
                        OrderTicket::factory()->count(rand(1, 2))->create([
                            'order_id' => $order->id,
                            'ticket_category_id' => $cat->id,
                            'unit_price' => $cat->price,
                        ]);

                        // Add merch to some orders
                        if (rand(1, 10) > 7) {
                            $variant = MerchandiseVariant::whereHas('item', function ($q) use ($event) {
                                $q->where('event_id', $event->id);
                            })->inRandomOrder()->first();

                            if ($variant) {
                                OrderMerchandise::factory()->create([
                                    'order_id' => $order->id,
                                    'merchandise_variant_id' => $variant->id,
                                    'unit_price' => $variant->item->base_price + $variant->price_adjustment,
                                    'quantity' => rand(1, 2),
                                ]);
                            }
                        }

                        // Update order total
                        $order->update([
                            'total_amount' => $order->tickets->sum('unit_price') + $order->merchandise->sum(fn ($m) => $m->unit_price * $m->quantity),
                        ]);
                    }
                }

                // Seed Cancellation Request and Payout depending on status
                if ($event->status->value === 'awaiting_cancellation') {
                    CancellationRequest::factory()->create([
                        'event_id' => $event->id,
                        'requested_by' => $organizer->id,
                        'status' => 'pending',
                        'reason' => 'Saya harus membatalkan acara ini karena alasan darurat medis dan kendala logistik di lapangan.',
                    ]);

                    $revenue = Order::where('event_id', $event->id)->where('status', 'paid')->sum('total_amount');
                    $fee = $revenue * 0.10;

                    Payout::factory()->create([
                        'event_id' => $event->id,
                        'organizer_id' => $organizer->id,
                        'gross_amount' => $revenue,
                        'platform_fee' => $fee,
                        'net_amount' => $revenue - $fee,
                        'status' => 'pending',
                        'fee_percentage' => 10.00,
                    ]);
                } elseif ($event->status->value === 'cancelled') {
                    $admin = User::where('role', UserRole::Admin)->first();
                    CancellationRequest::factory()->create([
                        'event_id' => $event->id,
                        'requested_by' => $organizer->id,
                        'status' => 'approved',
                        'reason' => 'Masalah teknis yang tidak dapat diselesaikan dengan vendor utama.',
                        'reviewed_by' => $admin?->id,
                        'reviewed_at' => now(),
                    ]);

                    $revenue = Order::where('event_id', $event->id)->where('status', 'paid')->sum('total_amount');
                    $fee = $revenue * 0.10;

                    Payout::factory()->create([
                        'event_id' => $event->id,
                        'organizer_id' => $organizer->id,
                        'gross_amount' => $revenue,
                        'platform_fee' => $fee,
                        'net_amount' => $revenue - $fee,
                        'status' => 'voided',
                        'fee_percentage' => 10.00,
                    ]);
                } elseif ($event->status->value === 'completed') {
                    $revenue = Order::where('event_id', $event->id)->where('status', 'paid')->sum('total_amount');
                    $fee = $revenue * 0.10;

                    Payout::factory()->create([
                        'event_id' => $event->id,
                        'organizer_id' => $organizer->id,
                        'gross_amount' => $revenue,
                        'platform_fee' => $fee,
                        'net_amount' => $revenue - $fee,
                        'status' => 'completed',
                        'fee_percentage' => 10.00,
                    ]);
                } elseif ($event->status->value === 'published' && rand(1, 10) > 8) {
                    $admin = User::where('role', UserRole::Admin)->first();
                    CancellationRequest::factory()->create([
                        'event_id' => $event->id,
                        'requested_by' => $organizer->id,
                        'status' => 'rejected',
                        'reason' => 'Pengen batalin aja sepi peminat.',
                        'reviewed_by' => $admin?->id,
                        'rejection_reason' => 'Alasan pembatalan tidak memenuhi syarat minimum (tidak ada kondisi force majeure atau darurat).',
                        'reviewed_at' => now(),
                    ]);
                }
            }
        });
    }
}
