<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    // ── isCreator ─────────────────────────────────────────────

    public function test_is_creator_returns_true_for_creator_role(): void
    {
        $user = User::factory()->creator()->create();
        $this->assertTrue($user->isCreator());
    }

    public function test_is_creator_returns_false_for_non_creator_roles(): void
    {
        foreach ([UserRole::Admin, UserRole::Publisher, UserRole::Viewer, UserRole::Ghost] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertFalse($user->isCreator(), "isCreator() must be false for role: {$role->value}");
        }
    }

    // ── isAdmin ───────────────────────────────────────────────

    public function test_is_admin_returns_true_for_creator_and_admin(): void
    {
        $this->assertTrue(User::factory()->creator()->create()->isAdmin());
        $this->assertTrue(User::factory()->admin()->create()->isAdmin());
    }

    public function test_is_admin_returns_false_for_publisher_viewer_ghost(): void
    {
        foreach ([UserRole::Publisher, UserRole::Viewer, UserRole::Ghost] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertFalse($user->isAdmin(), "isAdmin() must be false for role: {$role->value}");
        }
    }

    // ── isGhost ───────────────────────────────────────────────

    public function test_is_ghost_returns_true_only_for_ghost_role(): void
    {
        $this->assertTrue(User::factory()->ghost()->create()->isGhost());
    }

    public function test_is_ghost_returns_false_for_non_ghost_roles(): void
    {
        foreach ([UserRole::Creator, UserRole::Admin, UserRole::Publisher, UserRole::Viewer] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertFalse($user->isGhost(), "isGhost() must be false for role: {$role->value}");
        }
    }

    // ── isBanned ──────────────────────────────────────────────

    public function test_is_banned_returns_false_when_no_ban(): void
    {
        $user = User::factory()->create(['banned_until' => null]);
        $this->assertFalse($user->isBanned());
    }

    public function test_is_banned_returns_true_when_ban_is_in_future(): void
    {
        $user = User::factory()->create(['banned_until' => now()->addDays(30)]);
        $this->assertTrue($user->isBanned());
    }

    public function test_is_banned_returns_false_when_ban_has_expired(): void
    {
        $user = User::factory()->create(['banned_until' => now()->subSecond()]);
        $this->assertFalse($user->isBanned());
    }

    // ── isOnline ──────────────────────────────────────────────

    public function test_is_online_returns_true_when_active_within_2_minutes(): void
    {
        $user = User::factory()->create(['last_seen_at' => now()->subMinute()]);
        $this->assertTrue($user->isOnline());
    }

    public function test_is_online_returns_false_when_inactive_longer_than_2_minutes(): void
    {
        $user = User::factory()->create(['last_seen_at' => now()->subMinutes(3)]);
        $this->assertFalse($user->isOnline());
    }

    public function test_is_online_returns_false_when_never_seen(): void
    {
        $user = User::factory()->create(['last_seen_at' => null]);
        $this->assertFalse($user->isOnline());
    }

    // ── ban() ─────────────────────────────────────────────────

    public function test_first_ban_lasts_30_days_and_increments_count(): void
    {
        $user = User::factory()->create(['ban_count' => 0, 'banned_until' => null]);
        $user->ban();

        $fresh = $user->fresh();
        $this->assertEquals(1, $fresh->ban_count);
        $this->assertTrue($fresh->isBanned());
        $this->assertEqualsWithDelta(now()->addDays(30)->timestamp, $fresh->banned_until->timestamp, 5);
    }

    public function test_second_ban_lasts_90_days(): void
    {
        $user = User::factory()->create(['ban_count' => 1, 'banned_until' => null]);
        $user->ban();

        $fresh = $user->fresh();
        $this->assertEquals(2, $fresh->ban_count);
        $this->assertEqualsWithDelta(now()->addDays(90)->timestamp, $fresh->banned_until->timestamp, 5);
    }

    public function test_third_and_subsequent_bans_are_permanent(): void
    {
        foreach ([2, 3, 5] as $existingCount) {
            $user = User::factory()->create(['ban_count' => $existingCount, 'banned_until' => null]);
            $user->ban();

            $fresh = $user->fresh();
            $this->assertGreaterThan(
                now()->addYears(50)->timestamp,
                $fresh->banned_until->timestamp,
                "Ban #{$existingCount} should be permanent"
            );
        }
    }

    public function test_ban_always_increments_ban_count(): void
    {
        $user = User::factory()->create(['ban_count' => 0]);
        $user->ban();
        $this->assertEquals(1, $user->fresh()->ban_count);

        $user->ban();
        $this->assertEquals(2, $user->fresh()->ban_count);
    }

    // ── Relationships ─────────────────────────────────────────

    public function test_user_has_many_packages(): void
    {
        $user = User::factory()->publisher()->create();
        \App\Models\Package::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->packages);
    }

    public function test_user_has_many_builds(): void
    {
        $user = User::factory()->publisher()->create();
        \App\Models\Build::factory()->count(2)->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->builds);
    }
}
