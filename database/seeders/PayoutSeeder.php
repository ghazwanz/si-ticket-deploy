<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PayoutSeeder extends Seeder
{
    public function run(): void
    {
        $organizer = User::where('email', 'organizer@joinfest.com')->first();
        $admin = User::where('email', 'admin@joinfest.com')->first();

        $eventId = Str::uuid()->toString();
        $categoryId = DB::table('event_categories')->first()->id;

        DB::table('events')->insert([
            'id' => $eventId,
            'organizer_id' => $organizer->id,
            'category_id' => $categoryId,
            'name' => 'Past Completed Event',
            'slug' => Str::slug('Past Completed Event'),
            'description' => '<p>Event that has already finished.</p>',
            'venue_name' => 'GOR',
            'address' => 'Jl. Kebon',
            'city' => 'Jakarta',
            'event_date' => now()->subDays(10)->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '18:00:00',
            'status' => 'completed',
            'is_featured' => false,
            'created_at' => now()->subDays(30),
            'updated_at' => now()->subDays(10),
        ]);

        DB::table('payouts')->insert([
            'id' => Str::uuid()->toString(),
            'event_id' => $eventId,
            'organizer_id' => $organizer->id,
            'gross_amount' => 50000000,
            'platform_fee' => 2500000, // 5%
            'net_amount' => 47500000,
            'fee_percentage' => 5.00,
            'payout_bank_name' => 'BCA',
            'payout_account_number' => '1234567890',
            'payout_account_holder' => 'Event Organizer',
            'missing_bank_details' => false,
            'status' => 'completed',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now()->subDays(2),
            'disbursed_by' => $admin->id,
            'disbursed_at' => now()->subDays(1),
            'transfer_reference' => 'WD-'.Str::random(10),
            'proof_photo' => 'payouts/proofs/dummy.jpg',
            'created_at' => now()->subDays(9),
            'updated_at' => now()->subDays(1),
        ]);
    }
}
