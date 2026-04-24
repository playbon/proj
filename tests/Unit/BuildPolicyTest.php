<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\Build;
use App\Models\User;
use App\Policies\BuildPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuildPolicyTest extends TestCase
{
    use RefreshDatabase;

    private BuildPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new BuildPolicy();
    }

    // ── view ──────────────────────────────────────────────────

    public function test_view_returns_true_for_all_roles(): void
    {
        $build = Build::factory()->create();
        foreach (UserRole::cases() as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertTrue($this->policy->view($user, $build), "view must be true for {$role->value}");
        }
    }

    // ── create ────────────────────────────────────────────────

    public function test_create_allowed_for_creator_admin_publisher(): void
    {
        foreach ([UserRole::Creator, UserRole::Admin, UserRole::Publisher] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertTrue($this->policy->create($user), "create must be allowed for {$role->value}");
        }
    }

    public function test_create_denied_for_viewer_and_ghost(): void
    {
        foreach ([UserRole::Viewer, UserRole::Ghost] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertFalse($this->policy->create($user), "create must be denied for {$role->value}");
        }
    }

    // ── delete ────────────────────────────────────────────────

    public function test_creator_can_delete_any_build(): void
    {
        $creator = User::factory()->creator()->create();
        $owner   = User::factory()->publisher()->create();
        $build   = Build::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($this->policy->delete($creator, $build));
    }

    public function test_admin_can_delete_publisher_build(): void
    {
        $admin     = User::factory()->admin()->create();
        $publisher = User::factory()->publisher()->create();
        $build     = Build::factory()->create(['user_id' => $publisher->id]);

        $this->assertTrue($this->policy->delete($admin, $build));
    }

    public function test_admin_cannot_delete_creator_build(): void
    {
        $admin   = User::factory()->admin()->create();
        $creator = User::factory()->creator()->create();
        $build   = Build::factory()->create(['user_id' => $creator->id]);

        $this->assertFalse($this->policy->delete($admin, $build));
    }

    public function test_publisher_can_delete_own_build(): void
    {
        $publisher = User::factory()->publisher()->create();
        $build     = Build::factory()->create(['user_id' => $publisher->id]);

        $this->assertTrue($this->policy->delete($publisher, $build));
    }

    public function test_publisher_cannot_delete_others_build(): void
    {
        $p1    = User::factory()->publisher()->create();
        $p2    = User::factory()->publisher()->create();
        $build = Build::factory()->create(['user_id' => $p2->id]);

        $this->assertFalse($this->policy->delete($p1, $build));
    }

    public function test_ghost_cannot_delete_any_build(): void
    {
        $ghost = User::factory()->ghost()->create();
        $build = Build::factory()->create(['user_id' => $ghost->id]);

        $this->assertFalse($this->policy->delete($ghost, $build));
    }

    public function test_viewer_cannot_delete_own_build(): void
    {
        $viewer = User::factory()->viewer()->create();
        $build  = Build::factory()->create(['user_id' => $viewer->id]);

        $this->assertFalse($this->policy->delete($viewer, $build));
    }
}
