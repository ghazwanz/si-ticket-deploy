<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin
        User::firstOrCreate([
            'email' => 'admin@joinfest.com',
        ], [
            'id' => Str::uuid()->toString(),
            'name' => 'System Administrator',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Organizer
        $organizer = User::firstOrCreate([
            'email' => 'organizer@joinfest.com',
        ], [
            'id' => Str::uuid()->toString(),
            'name' => 'Event Organizer',
            'password' => Hash::make('password'),
            'role' => UserRole::Organizer,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        DB::table('organizer_profiles')->insertOrIgnore([
            'id' => Str::uuid()->toString(),
            'user_id' => $organizer->id,
            'organization_name' => 'Bintang Kreasindo EO',
            'phone' => '081234567890',
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name' => 'Bintang Kreasindo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Regular User
        User::firstOrCreate([
            'email' => 'user@joinfest.com',
        ], [
            'id' => Str::uuid()->toString(),
            'name' => 'Regular User',
            'password' => Hash::make('password'),
            'role' => UserRole::User,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Generate additional mock users
        User::factory()->count(10)->create();
    }
}
