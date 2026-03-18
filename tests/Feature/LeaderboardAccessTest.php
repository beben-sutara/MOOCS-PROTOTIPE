<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_opening_leaderboard()
    {
        $response = $this->get(route('leaderboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_can_view_leaderboard_page()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get(route('leaderboard'));

        $response->assertOk();
        $response->assertSee('Leaderboard');
    }

    public function test_instructor_cannot_view_leaderboard_page()
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
        ]);

        $response = $this->actingAs($instructor)->get(route('leaderboard'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error', 'Leaderboard hanya tersedia untuk pengguna biasa.');
    }

    public function test_admin_cannot_view_leaderboard_page()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('leaderboard'));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHas('error', 'Leaderboard hanya tersedia untuk pengguna biasa.');
    }
}
