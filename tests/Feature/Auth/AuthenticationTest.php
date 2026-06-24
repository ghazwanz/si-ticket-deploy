<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_login_with_missing_csrf_token_redirects_back_to_login_with_message(): void
    {
        $this->app->singleton(ValidateCsrfToken::class, function ($app) {
            return new class($app, $app->make(Encrypter::class)) extends ValidateCsrfToken
            {
                protected function runningUnitTests()
                {
                    return false;
                }
            };
        });

        $response = $this->withMiddleware()
            ->from('/login')
            ->post('/login', [
                'email' => 'invalid@example.com',
                'password' => 'password',
            ]);

        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHas('status', 'Sesi kamu sudah berakhir. Silakan login ulang.');
    }

    public function test_users_can_logout(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_user_role_checkout_intended_redirects_to_checkout(): void
    {
        $user = User::factory()->create(['role' => UserRole::User]);

        $response = $this->withSession(['url.intended' => '/checkout'])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/checkout');
    }

    public function test_admin_role_checkout_intended_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $response = $this->withSession(['url.intended' => '/checkout'])
            ->post('/login', [
                'email' => $admin->email,
                'password' => 'password',
            ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $follow = $this->actingAs($admin)->get(route('dashboard'));
        $follow->assertRedirect(route('admin.dashboard'));
    }

    public function test_organizer_role_checkout_intended_redirects_to_organizer_dashboard(): void
    {
        $organizer = User::factory()->create(['role' => UserRole::Organizer]);

        $response = $this->withSession(['url.intended' => '/checkout'])
            ->post('/login', [
                'email' => $organizer->email,
                'password' => 'password',
            ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $follow = $this->actingAs($organizer)->get(route('dashboard'));
        $follow->assertRedirect(route('organizer.dashboard'));
    }
}
