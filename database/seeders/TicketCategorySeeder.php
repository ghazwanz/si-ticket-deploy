<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketCategorySeeder extends Seeder
{
    public function run(): void
    {
        $eventId = DB::table('events')->where('slug', Str::slug('Neon Nights World Tour 2026'))->value('id');

        // Ticket Categories
        $ticketId1 = Str::uuid()->toString();
        $ticketId2 = Str::uuid()->toString();
        DB::table('ticket_categories')->insert([
            [
                'id' => $ticketId1,
                'event_id' => $eventId,
                'name' => 'Festifal A (Standing)',
                'description' => 'Area berdiri paling dekat dengan panggung.',
                'price' => 750000,
                'quota' => 2000,
                'max_per_user' => 4,
                'sold_count' => 150,
                'sale_start_at' => now()->subDays(5),
                'sale_end_at' => now()->addMonths(6),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => $ticketId2,
                'event_id' => $eventId,
                'name' => 'VIP',
                'description' => 'Area duduk VIP dengan akses jalur khusus.',
                'price' => 1500000,
                'quota' => 500,
                'max_per_user' => 4,
                'sold_count' => 50,
                'sale_start_at' => now()->subDays(5),
                'sale_end_at' => now()->addMonths(6),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Merchandise Items & Variants
        $merchId = Str::uuid()->toString();
        DB::table('merchandise_items')->insert([
            'id' => $merchId,
            'event_id' => $eventId,
            'name' => 'Kaos Orisinil Neon Nights',
            'description' => 'Kaos event resmi edisi terbatas.',
            'image' => 'KaosOfficial.png',
            'base_price' => 250000,
            'is_available' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $variantId = Str::uuid()->toString();
        DB::table('merchandise_variants')->insert([
            'id' => $variantId,
            'merchandise_item_id' => $merchId,
            'variant_group' => 'Size '.Str::random(5),
            'variant_value' => 'L',
            'stock' => 100,
            'price_adjustment' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
