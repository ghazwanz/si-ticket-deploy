<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\OrganizerStatus;
use App\Enums\UserRole;
use App\Mail\OrganizerApprovalMail;
use App\Mail\OrganizerRejectionMail;
use App\Models\OrganizerProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class OrganizerApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_organizers_can_register_with_legality_document(): void
    {
        Storage::fake('public');

        $response = $this->post('/register', [
            'name' => 'Organizer Test',
            'email' => 'organizer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'organizer',
            'organization_name' => 'Test Org',
            'phone' => '08123456789',
            'organization_address' => 'Jl. Test No. 10',
            'official_contact' => 'test@org.com',
            'bank_name' => 'BCA',
            'bank_account_number' => '12345678',
            'bank_account_name' => 'Test Org Owner',
            'legality_document' => UploadedFile::fake()->create('legality.pdf', 100),
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'organizer@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(UserRole::Organizer, $user->role);

        $profile = $user->organizerProfile;
        $this->assertNotNull($profile);
        $this->assertEquals(OrganizerStatus::Pending, $profile->status);
        $this->assertEquals('Test Org', $profile->organization_name);
        $this->assertEquals('Jl. Test No. 10', $profile->organization_address);
        $this->assertEquals('test@org.com', $profile->official_contact);
        $this->assertNotNull($profile->legality_document);

        Storage::disk('public')->assertExists($profile->legality_document);
    }

    public function test_unapproved_organizers_are_blocked_from_restricted_routes(): void
    {
        $organizer = User::factory()->organizer()->create();
        $profile = OrganizerProfile::factory()->create([
            'user_id' => $organizer->id,
            'status' => OrganizerStatus::Pending,
        ]);

        $this->actingAs($organizer);

        // Try to access events index
        $response = $this->get(route('organizer.events.index'));
        $response->assertRedirect(route('organizer.dashboard'));
        $response->assertSessionHas('error');

        // Try to access payouts index
        $response = $this->get(route('organizer.payouts.index'));
        $response->assertRedirect(route('organizer.dashboard'));
        $response->assertSessionHas('error');

        // Try to access scanner index
        $response = $this->get(route('organizer.scanner.index'));
        $response->assertRedirect(route('organizer.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_admin_can_approve_pending_organizer_and_mail_is_sent(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create();
        $profile = OrganizerProfile::factory()->create([
            'user_id' => $organizer->id,
            'status' => OrganizerStatus::Pending,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.approve-organizer', $organizer));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertEquals(OrganizerStatus::Approved, $profile->refresh()->status);

        Mail::assertSent(OrganizerApprovalMail::class, function ($mail) use ($organizer) {
            return $mail->hasTo($organizer->email);
        });
    }

    public function test_admin_can_reject_pending_organizer_and_mail_is_sent(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create();
        $profile = OrganizerProfile::factory()->create([
            'user_id' => $organizer->id,
            'status' => OrganizerStatus::Pending,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.reject-organizer', $organizer), [
            'rejection_reason' => 'Dokumen tidak valid.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $profile->refresh();
        $this->assertEquals(OrganizerStatus::Rejected, $profile->status);
        $this->assertEquals('Dokumen tidak valid.', $profile->rejection_reason);

        Mail::assertSent(OrganizerRejectionMail::class, function ($mail) use ($organizer) {
            return $mail->hasTo($organizer->email) && $mail->rejectionReason === 'Dokumen tidak valid.';
        });
    }

    public function test_rejected_organizer_resubmission_resets_status_to_pending(): void
    {
        Storage::fake('public');

        $organizer = User::factory()->organizer()->create([
            'name' => 'Old Name',
            'email' => 'organizer@example.com',
        ]);
        $profile = OrganizerProfile::factory()->create([
            'user_id' => $organizer->id,
            'status' => OrganizerStatus::Rejected,
            'rejection_reason' => 'Dokumen tidak valid.',
        ]);

        $this->actingAs($organizer);

        $response = $this->put(route('organizer.settings.profile'), [
            'name' => 'New Name',
            'email' => 'organizer@example.com',
            'organization_name' => 'Updated Org',
            'phone' => '08123456789',
            'organization_address' => 'Jl. Baru No. 20',
            'official_contact' => 'new@org.com',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '87654321',
            'bank_account_name' => 'New Owner Name',
            'legality_document' => UploadedFile::fake()->create('new_legality.pdf', 100),
        ]);

        $response->assertRedirect();

        $profile->refresh();
        $this->assertEquals(OrganizerStatus::Pending, $profile->status);
        $this->assertNull($profile->rejection_reason);
        $this->assertEquals('Updated Org', $profile->organization_name);
        $this->assertEquals('Jl. Baru No. 20', $profile->organization_address);
        $this->assertEquals('new@org.com', $profile->official_contact);
        $this->assertNotNull($profile->legality_document);

        Storage::disk('public')->assertExists($profile->legality_document);
    }

    public function test_admin_can_create_organizer_with_additional_fields(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Admin Created Organizer',
            'email' => 'admin_org@example.com',
            'password' => 'password123',
            'role' => 'organizer',
            'is_active' => 1,
            'organization_name' => 'Admin Org',
            'phone' => '08987654321',
            'bank_name' => 'BCA',
            'bank_account_number' => '99999999',
            'bank_account_name' => 'Admin Org Account',
            'organization_address' => 'Jl. Admin No. 5',
            'official_contact' => 'admin_official@org.com',
            'legality_document' => UploadedFile::fake()->create('admin_legality.pdf', 100),
        ]);

        $response->assertRedirect();

        $user = User::where('email', 'admin_org@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(UserRole::Organizer, $user->role);

        $profile = $user->organizerProfile;
        $this->assertNotNull($profile);
        $this->assertEquals(OrganizerStatus::Pending, $profile->status);
        $this->assertEquals('Admin Org', $profile->organization_name);
        $this->assertEquals('Jl. Admin No. 5', $profile->organization_address);
        $this->assertEquals('admin_official@org.com', $profile->official_contact);
        $this->assertNotNull($profile->legality_document);
        Storage::disk('public')->assertExists($profile->legality_document);
    }

    public function test_admin_can_update_organizer_with_additional_fields(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();
        $organizer = User::factory()->organizer()->create();
        $profile = OrganizerProfile::factory()->create([
            'user_id' => $organizer->id,
            'status' => OrganizerStatus::Approved,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.users.update', $organizer), [
            'name' => 'Updated Organizer Name',
            'email' => $organizer->email,
            'role' => 'organizer',
            'is_active' => 1,
            'organization_name' => 'Updated Admin Org',
            'phone' => '08777777777',
            'bank_name' => 'Mandiri',
            'bank_account_number' => '11112222',
            'bank_account_name' => 'Updated Account Name',
            'organization_address' => 'Jl. Admin Baru No. 8',
            'official_contact' => 'updated_official@org.com',
            'legality_document' => UploadedFile::fake()->create('updated_legality.pdf', 100),
        ]);

        $response->assertRedirect();

        $profile->refresh();
        $this->assertEquals('Updated Admin Org', $profile->organization_name);
        $this->assertEquals('Jl. Admin Baru No. 8', $profile->organization_address);
        $this->assertEquals('updated_official@org.com', $profile->official_contact);
        $this->assertNotNull($profile->legality_document);
        Storage::disk('public')->assertExists($profile->legality_document);
    }
}
