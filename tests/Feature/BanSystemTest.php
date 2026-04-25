<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BanSystemTest extends TestCase
{
    use RefreshDatabase;

    // ── Middleware ────────────────────────────────────────────

    public function test_banned_user_is_redirected_to_banned_page(): void
    {
        $user = User::factory()->banned()->publisher()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('banned'));
    }

    public function test_banned_user_can_access_banned_page(): void
    {
        $user = User::factory()->banned()->publisher()->create();

        $this->actingAs($user)
            ->get(route('banned'))
            ->assertOk();
    }

    public function test_banned_user_can_logout(): void
    {
        $user = User::factory()->banned()->publisher()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_not_banned_user_can_access_dashboard(): void
    {
        $user = User::factory()->publisher()->create(['banned_until' => null]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_user_with_expired_ban_can_access_dashboard(): void
    {
        $user = User::factory()->publisher()->create([
            'banned_until' => now()->subDay(),
            'ban_count'    => 1,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_banned_user_cannot_access_packages(): void
    {
        $user = User::factory()->banned()->publisher()->create();

        $this->actingAs($user)
            ->get(route('packages.index'))
            ->assertRedirect(route('banned'));
    }

    public function test_banned_user_cannot_create_packages(): void
    {
        $user = User::factory()->banned()->publisher()->create();

        $this->actingAs($user)
            ->post(route('packages.store'), ['name' => 'Test'])
            ->assertRedirect(route('banned'));
    }

    // ── Progressive ban logic ──────────────────────────────────

    public function test_ban_count_increases_with_each_ban(): void
    {
        $user = User::factory()->publisher()->create(['ban_count' => 0]);

        $user->ban();
        $this->assertEquals(1, $user->fresh()->ban_count);

        $user->ban();
        $this->assertEquals(2, $user->fresh()->ban_count);

        $user->ban();
        $this->assertEquals(3, $user->fresh()->ban_count);
    }

    public function test_banned_user_cannot_access_builds(): void
    {
        $user = User::factory()->banned()->publisher()->create();

        $this->actingAs($user)
            ->get(route('builds.index'))
            ->assertRedirect(route('banned'));
    }
}
