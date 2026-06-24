<?php

namespace Tests\Feature\Organizer;

use App\Enums\OrganizerStatus;
use App\Enums\UserRole;
use App\Models\OrganizerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test organizer settings page rendering.
     */
    public function test_organizer_settings_page_can_be_rendered(): void
    {
        $user = User::factory()->create(['role' => UserRole::Organizer]);

        OrganizerProfile::create([
            'user_id' => $user->id,
            'organization_name' => 'Test Org',
            'phone' => '1234567890',
            'organization_address' => 'Jl. Test No. 123',
            'official_contact' => 'test@bintang.com',
            'bank_name' => 'BCA',
            'bank_account_number' => '123456',
            'bank_account_name' => 'Test Holder',
            'status' => OrganizerStatus::Approved,
        ]);

        $response = $this->actingAs($user)->get(route('organizer.settings'));

        $response->assertStatus(200);
        $response->assertSee('Test Org');
    }

    /**
     * Test organizer settings update functionality.
     */
    public function test_organizer_can_update_profile_and_organization_details(): void
    {
        $user = User::factory()->create(['role' => UserRole::Organizer]);
        $oldEmail = $user->email;
        OrganizerProfile::create([
            'user_id' => $user->id,
            'organization_name' => 'Test Org',
            'phone' => '1234567890',
            'organization_address' => 'Jl. Test No. 123',
            'official_contact' => 'test@bintang.com',
            'bank_name' => 'BCA',
            'bank_account_number' => '123456',
            'bank_account_name' => 'Test Holder',
            'status' => OrganizerStatus::Approved,
        ]);

        // Scenario 1: Email changed (should set pending_email and return verification-link-sent)
        $response = $this->actingAs($user)->put(route('organizer.settings.profile'), [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'organization_name' => 'Updated Org Name',
            'phone' => '08123456789',
            'organization_address' => 'Jl. Baru No. 456',
            'official_contact' => 'updated@bintang.com',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '987654321',
            'bank_account_name' => 'Updated Holder Name',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'verification-link-sent');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => $oldEmail,
            'pending_email' => 'updated@example.com',
        ]);

        // Scenario 2: Email unchanged (should update details and return profile-updated)
        $response = $this->actingAs($user)->put(route('organizer.settings.profile'), [
            'name' => 'Updated Name',
            'email' => $oldEmail,
            'organization_name' => 'Updated Org Name',
            'phone' => '08123456789',
            'organization_address' => 'Jl. Baru No. 456',
            'official_contact' => 'updated@bintang.com',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '987654321',
            'bank_account_name' => 'Updated Holder Name',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'profile-updated');

        $this->assertDatabaseHas('organizer_profiles', [
            'user_id' => $user->id,
            'organization_name' => 'Updated Org Name',
            'phone' => '08123456789',
            'organization_address' => 'Jl. Baru No. 456',
            'official_contact' => 'updated@bintang.com',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '987654321',
            'bank_account_name' => 'Updated Holder Name',
        ]);
    }

    public function test_organizer_can_upload_and_delete_profile_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => UserRole::Organizer]);
        OrganizerProfile::create([
            'user_id' => $user->id,
            'organization_name' => 'Test Org',
            'phone' => '1234567890',
            'organization_address' => 'Jl. Test No. 123',
            'official_contact' => 'test@bintang.com',
            'bank_name' => 'BCA',
            'bank_account_number' => '123456',
            'bank_account_name' => 'Test Holder',
            'status' => OrganizerStatus::Approved,
        ]);

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user)->put(route('organizer.settings.profile'), [
            'name' => $user->name,
            'email' => $user->email,
            'organization_name' => 'Test Org',
            'phone' => '1234567890',
            'organization_address' => 'Jl. Test No. 123',
            'official_contact' => 'test@bintang.com',
            'bank_name' => 'BCA',
            'bank_account_number' => '123456',
            'bank_account_name' => 'Test Holder',
            'profile_photo' => $file,
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('public')->assertExists($user->profile_photo_path);

        // test deletion
        $response = $this->actingAs($user)->put(route('organizer.settings.profile'), [
            'name' => $user->name,
            'email' => $user->email,
            'organization_name' => 'Test Org',
            'phone' => '1234567890',
            'organization_address' => 'Jl. Test No. 123',
            'official_contact' => 'test@bintang.com',
            'bank_name' => 'BCA',
            'bank_account_number' => '123456',
            'bank_account_name' => 'Test Holder',
            'remove_photo' => '1',
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertNull($user->profile_photo_path);
    }

    /**
     * Test organizer settings password update functionality.
     */
    public function test_organizer_can_update_password(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Organizer,
            'password' => Hash::make('password123'),
        ]);

        $response = $this->actingAs($user)->put(route('organizer.settings.password'), [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'password-updated');

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    /**
     * Test non-organizer role block on settings.
     */
    public function test_non_organizer_cannot_access_organizer_settings(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $response = $this->actingAs($user)->get(route('organizer.settings'));

        $response->assertStatus(403);
    }
}
