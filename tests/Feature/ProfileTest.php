<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated_with_pending_email(): void
    {
        $user = User::factory()->create();
        $oldEmail = $user->email;

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame($oldEmail, $user->email);
        $this->assertSame('test@example.com', $user->pending_email);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_pending_email_can_be_verified(): void
    {
        $user = User::factory()->create();
        $user->pending_email = 'new@example.com';
        $user->save();

        $verificationUrl = URL::temporarySignedRoute(
            'profile.verify-pending-email',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('new@example.com')]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect('/profile');
        $user->refresh();
        $this->assertSame('new@example.com', $user->email);
        $this->assertNull($user->pending_email);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_user_can_upload_and_delete_profile_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo' => $file,
            ]);

        $response->assertRedirect('/profile');
        $user->refresh();
        $this->assertProjectPhotoExists($user->profile_photo_path);

        // test deletion
        $response = $this->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'remove_photo' => '1',
            ]);

        $response->assertRedirect('/profile');
        $user->refresh();
        $this->assertNull($user->profile_photo_path);
    }

    private function assertProjectPhotoExists(?string $path): void
    {
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertTrue($user->fresh()->trashed());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
