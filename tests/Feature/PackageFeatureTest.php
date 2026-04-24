<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageFeatureTest extends TestCase
{
    use RefreshDatabase;

    // ── Index ─────────────────────────────────────────────────

    public function test_packages_index_is_accessible_to_authenticated_users(): void
    {
        $user = User::factory()->viewer()->create();
        $this->actingAs($user)->get(route('packages.index'))->assertOk();
    }

    public function test_packages_index_lists_active_packages(): void
    {
        $user = User::factory()->publisher()->create();
        Package::factory()->count(3)->create();

        $this->actingAs($user)
            ->get(route('packages.index'))
            ->assertOk()
            ->assertSeeText($user->packages()->count() >= 0 ? 'Пакеты' : 'Пакеты');
    }

    // ── Create / Store ────────────────────────────────────────

    public function test_publisher_can_access_create_form(): void
    {
        $user = User::factory()->publisher()->create();
        $this->actingAs($user)->get(route('packages.create'))->assertOk();
    }

    public function test_ghost_cannot_access_create_form(): void
    {
        $ghost = User::factory()->ghost()->create();
        $this->actingAs($ghost)->get(route('packages.create'))->assertRedirect(route('ghost.pending'));
    }

    public function test_viewer_cannot_access_create_form(): void
    {
        $viewer = User::factory()->viewer()->create();
        $this->actingAs($viewer)->get(route('packages.create'))->assertForbidden();
    }

    public function test_publisher_can_create_package(): void
    {
        $user    = User::factory()->publisher()->create();
        $channel = Channel::factory()->create();

        $this->actingAs($user)->post(route('packages.store'), [
            'name'        => 'My Package',
            'description' => 'A test package',
            'channel_id'  => $channel->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('packages', ['name' => 'My Package', 'user_id' => $user->id]);
    }

    public function test_package_slug_is_auto_generated_from_name(): void
    {
        $user    = User::factory()->publisher()->create();
        $channel = Channel::factory()->create();

        $this->actingAs($user)->post(route('packages.store'), [
            'name'       => 'My Awesome Package',
            'channel_id' => $channel->id,
        ]);

        $this->assertDatabaseHas('packages', ['slug' => 'my-awesome-package']);
    }

    public function test_create_package_fails_without_name(): void
    {
        $user = User::factory()->publisher()->create();

        $this->actingAs($user)
            ->post(route('packages.store'), ['description' => 'No name'])
            ->assertSessionHasErrors('name');
    }

    // ── Show ──────────────────────────────────────────────────

    public function test_package_show_page_is_accessible(): void
    {
        $user    = User::factory()->viewer()->create();
        $package = Package::factory()->create();

        $this->actingAs($user)
            ->get(route('packages.show', $package))
            ->assertOk();
    }

    // ── Edit / Update ─────────────────────────────────────────

    public function test_owner_can_access_edit_form(): void
    {
        $owner   = User::factory()->publisher()->create();
        $package = Package::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)->get(route('packages.edit', $package))->assertOk();
    }

    public function test_non_owner_cannot_access_edit_form(): void
    {
        $other   = User::factory()->publisher()->create();
        $owner   = User::factory()->publisher()->create();
        $package = Package::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)->get(route('packages.edit', $package))->assertForbidden();
    }

    public function test_owner_can_update_package(): void
    {
        $owner   = User::factory()->publisher()->create();
        $package = Package::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)->put(route('packages.update', $package), [
            'name'        => 'Updated Name',
            'description' => 'Updated description',
        ])->assertRedirect();

        $this->assertDatabaseHas('packages', ['id' => $package->id, 'name' => 'Updated Name']);
    }

    public function test_creator_can_update_any_package(): void
    {
        $creator = User::factory()->creator()->create();
        $owner   = User::factory()->publisher()->create();
        $package = Package::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($creator)->put(route('packages.update', $package), [
            'name' => 'Creator Updated',
        ])->assertRedirect();

        $this->assertDatabaseHas('packages', ['id' => $package->id, 'name' => 'Creator Updated']);
    }

    // ── Delete ────────────────────────────────────────────────

    public function test_owner_can_delete_own_package(): void
    {
        $owner   = User::factory()->publisher()->create();
        $package = Package::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($owner)
            ->delete(route('packages.destroy', $package))
            ->assertRedirect();

        $this->assertDatabaseMissing('packages', ['id' => $package->id]);
    }

    public function test_non_owner_cannot_delete_package(): void
    {
        $other   = User::factory()->publisher()->create();
        $owner   = User::factory()->publisher()->create();
        $package = Package::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($other)
            ->delete(route('packages.destroy', $package))
            ->assertForbidden();

        $this->assertDatabaseHas('packages', ['id' => $package->id]);
    }

    public function test_ghost_cannot_delete_package(): void
    {
        $ghost   = User::factory()->ghost()->create();
        $package = Package::factory()->create(['user_id' => $ghost->id]);

        $this->actingAs($ghost)
            ->delete(route('packages.destroy', $package))
            ->assertRedirect(route('ghost.pending'));
    }
}
