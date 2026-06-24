<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $organizer = User::where('email', 'organizer@joinfest.com')->first();
        $categoryId = DB::table('event_categories')->where('slug', 'konser')->value('id');

        DB::table('events')->updateOrInsert(
            ['slug' => Str::slug('Neon Nights World Tour 2026')],
            [
                'id' => Str::uuid()->toString(),
                'organizer_id' => $organizer->id,
                'category_id' => $categoryId,
                'name' => 'Neon Nights World Tour 2026',
                'description' => '<p>Konser musik terbesar tahun ini, menghadirkan musisi kelas dunia dengan panggung megah.</p>',
                'banner_image' => 'eobanner.png',
                'venue_name' => 'Sleman City Hall',
                'address' => 'Jl. Magelang Km 9,6',
                'city' => 'Yogyakarta',
                'event_date' => '2026-12-04',
                'start_time' => '19:00:00',
                'end_time' => '23:00:00',
                'status' => 'published',
                'is_featured' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
