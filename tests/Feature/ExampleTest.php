<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test the home page shows the user's profile photo when it is uploaded.
     */
    public function test_the_application_home_page_shows_user_profile_photo_when_present(): void
    {
        $user = User::factory()->create([
            'profile_photo_path' => 'profile-photos/user.jpg',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee($user->avatar_url);
    }

    /**
     * Test the home page shows user initials when profile photo is absent.
     */
    public function test_the_application_home_page_shows_initials_when_profile_photo_is_absent(): void
    {
        $user = User::factory()->create([
            'profile_photo_path' => null,
            'name' => 'John Doe',
        ]);

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(200);
        $response->assertSee('JO');
    }
}
