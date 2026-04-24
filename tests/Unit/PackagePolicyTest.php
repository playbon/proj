<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\Package;
use App\Models\User;
use App\Policies\PackagePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackagePolicyTest extends TestCase
{
    use RefreshDatabase;

    private PackagePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new PackagePolicy();
    }

    // ── viewAny / view ────────────────────────────────────────

    public function test_view_any_returns_true_for_all_roles(): void
    {
        foreach (UserRole::cases() as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertTrue($this->policy->viewAny($user), "viewAny must be true for {$role->value}");
        }
    }

    public function test_view_returns_true_for_all_roles(): void
    {
        $package = Package::factory()->create();
        foreach (UserRole::cases() as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->assertTrue($this->policy->view($user, $package), "view must be true for {$role->value}");
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

    // ── update ────────────────────────────────────────────────

    public function test_creator_can_update_any_package(): void
    {
        $creator = User::factory()->creator()->create();
        $owner   = User::factory()->publisher()->create();
        $package = Package::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($this->policy->update($creator, $package));
    }

    public function test_owner_can_update_own_package(): void
    {
        $owner   = User::factory()->publisher()->create();
        $package = Package::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($this->policy->update($owner, $package));
    }

    public function test_non_owner_cannot_update_others_package(): void
    {
        $other   = User::factory()->publisher()->create();
        $owner   = User::factory()->publisher()->create();
        $package = Package::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->update($other, $package));
    }

    public function test_ghost_cannot_update_even_own_package(): void
    {
        $ghost   = User::factory()->ghost()->create();
        $package = Package::factory()->create(['user_id' => $ghost->id]);

        $this->assertFalse($this->policy->update($ghost, $package));
    }

    public function test_admin_cannot_update_others_package(): void
    {
        $admin   = User::factory()->admin()->create();
        $owner   = User::factory()->publisher()->create();
        $package = Package::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->update($admin, $package));
    }

    // ── delete ────────────────────────────────────────────────

    public function test_creator_can_delete_any_package(): void
    {
        $creator = User::factory()->creator()->create();
        $owner   = User::factory()->publisher()->create();
        $package = Package::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($this->policy->delete($creator, $package));
    }

    public function test_owner_can_delete_own_package(): void
    {
        $owner   = User::factory()->publisher()->create();
        $package = Package::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($this->policy->delete($owner, $package));
    }

    public function test_non_owner_cannot_delete_others_package(): void
    {
        $other   = User::factory()->publisher()->create();
        $owner   = User::factory()->publisher()->create();
        $package = Package::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->delete($other, $package));
    }

    public function test_ghost_cannot_delete_even_own_package(): void
    {
        $ghost   = User::factory()->ghost()->create();
        $package = Package::factory()->create(['user_id' => $ghost->id]);

        $this->assertFalse($this->policy->delete($ghost, $package));
    }
}
