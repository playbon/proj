<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoleTest extends TestCase
{
    use RefreshDatabase;

    // ── Admin panel access ────────────────────────────────────

    public function test_admin_can_access_users_page(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get(route('admin.users'))->assertOk();
    }

    public function test_publisher_cannot_access_admin_users_page(): void
    {
        $publisher = User::factory()->publisher()->create();
        $this->actingAs($publisher)->get(route('admin.users'))->assertForbidden();
    }

    public function test_viewer_cannot_access_admin_panel(): void
    {
        $viewer = User::factory()->viewer()->create();
        $this->actingAs($viewer)->get(route('admin.users'))->assertForbidden();
    }

    public function test_ghost_cannot_access_admin_panel(): void
    {
        $ghost = User::factory()->ghost()->create();
        $this->actingAs($ghost)->get(route('admin.users'))->assertForbidden();
    }

    // ── Role changes ──────────────────────────────────────────

    public function test_creator_can_change_any_user_role(): void
    {
        $creator = User::factory()->creator()->create();
        $target  = User::factory()->viewer()->create();

        $this->actingAs($creator)->put(route('admin.users.role', $target), [
            'role' => 'publisher',
        ])->assertRedirect();

        $this->assertEquals(UserRole::Publisher, $target->fresh()->role);
    }

    public function test_admin_can_change_non_admin_role(): void
    {
        $admin  = User::factory()->admin()->create();
        $target = User::factory()->viewer()->create();

        $this->actingAs($admin)->put(route('admin.users.role', $target), [
            'role' => 'publisher',
        ])->assertRedirect();

        $this->assertEquals(UserRole::Publisher, $target->fresh()->role);
    }

    public function test_admin_cannot_change_role_of_another_admin(): void
    {
        $admin       = User::factory()->admin()->create();
        $otherAdmin  = User::factory()->admin()->create();

        $this->actingAs($admin)->put(route('admin.users.role', $otherAdmin), [
            'role' => 'viewer',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertEquals(UserRole::Admin, $otherAdmin->fresh()->role);
    }

    public function test_publisher_cannot_change_roles(): void
    {
        $publisher = User::factory()->publisher()->create();
        $target    = User::factory()->viewer()->create();

        $this->actingAs($publisher)->put(route('admin.users.role', $target), [
            'role' => 'publisher',
        ])->assertForbidden();
    }

    // ── Channels ──────────────────────────────────────────────

    public function test_admin_can_create_channel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.channels.store'), [
            'name'        => 'Release',
            'slug'        => 'release',
            'description' => 'Production releases',
        ])->assertRedirect();

        $this->assertDatabaseHas('channels', ['slug' => 'release']);
    }

    public function test_publisher_cannot_create_channel(): void
    {
        $publisher = User::factory()->publisher()->create();

        $this->actingAs($publisher)->post(route('admin.channels.store'), [
            'name' => 'Hack',
            'slug' => 'hack',
        ])->assertForbidden();
    }

    // ── Upgrade requests ──────────────────────────────────────

    public function test_viewer_can_submit_upgrade_request(): void
    {
        $viewer = User::factory()->viewer()->create();

        $this->actingAs($viewer)->post(route('upgrade-request.store'), [
            'reason' => 'I want to publish packages for my team.',
        ])->assertRedirect();

        $this->assertDatabaseHas('role_upgrade_requests', [
            'user_id' => $viewer->id,
            'status'  => 'pending',
        ]);
    }

    public function test_viewer_cannot_submit_duplicate_upgrade_request(): void
    {
        $viewer = User::factory()->viewer()->create();

        $this->actingAs($viewer)->post(route('upgrade-request.store'), [
            'reason' => 'First request',
        ]);

        $this->actingAs($viewer)->post(route('upgrade-request.store'), [
            'reason' => 'Second request',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseCount('role_upgrade_requests', 1);
    }

    public function test_publisher_cannot_submit_upgrade_request(): void
    {
        $publisher = User::factory()->publisher()->create();

        $this->actingAs($publisher)->post(route('upgrade-request.store'), [
            'reason' => 'Already publisher',
        ])->assertRedirect()->assertSessionHas('error');
    }

    public function test_admin_can_approve_upgrade_request(): void
    {
        $admin  = User::factory()->admin()->create();
        $viewer = User::factory()->viewer()->create();

        $request = \App\Models\RoleUpgradeRequest::create([
            'user_id' => $viewer->id,
            'reason'  => 'Need publisher access',
            'status'  => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.upgrade-requests.approve', $request))
            ->assertRedirect();

        $this->assertEquals(UserRole::Publisher, $viewer->fresh()->role);
    }

    public function test_admin_can_reject_upgrade_request(): void
    {
        $admin  = User::factory()->admin()->create();
        $viewer = User::factory()->viewer()->create();

        $request = \App\Models\RoleUpgradeRequest::create([
            'user_id' => $viewer->id,
            'reason'  => 'Reason',
            'status'  => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.upgrade-requests.reject', $request))
            ->assertRedirect();

        $this->assertEquals('rejected', $request->fresh()->status);
        $this->assertEquals(UserRole::Viewer, $viewer->fresh()->role);
    }
}
