<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ── Login page ────────────────────────────────────────────

    public function test_login_page_is_accessible_to_guests(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_register_page_is_accessible_to_guests(): void
    {
        $this->get(route('register'))->assertOk();
    }

    public function test_authenticated_user_is_redirected_from_login(): void
    {
        $user = User::factory()->publisher()->create();
        $this->actingAs($user)->get(route('login'))->assertRedirect();
    }

    // ── Registration ──────────────────────────────────────────

    public function test_user_can_register_successfully(): void
    {
        $this->post(route('register'), [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('ghost.pending'));

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_newly_registered_user_has_ghost_role(): void
    {
        $this->post(route('register'), [
            'name'                  => 'Ghost User',
            'email'                 => 'ghost@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'ghost@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(UserRole::Ghost, $user->role);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->post(route('register'), [
            'name'                  => 'Another User',
            'email'                 => 'taken@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('email');
    }

    public function test_registration_fails_with_mismatched_passwords(): void
    {
        $this->post(route('register'), [
            'name'                  => 'User',
            'email'                 => 'user@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors('password');
    }

    public function test_registration_fails_without_required_fields(): void
    {
        $this->post(route('register'), [])->assertSessionHasErrors(['name', 'email', 'password']);
    }

    // ── Login ─────────────────────────────────────────────────

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->publisher()->create(['password' => bcrypt('secret123')]);

        $this->post(route('login'), [
            'email'    => $user->email,
            'password' => 'secret123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct')]);

        $this->post(route('login'), [
            'email'    => $user->email,
            'password' => 'wrong',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_fails_for_nonexistent_email(): void
    {
        $this->post(route('login'), [
            'email'    => 'nobody@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');
    }

    // ── Logout ────────────────────────────────────────────────

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->publisher()->create();
        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    // ── Guest redirect ────────────────────────────────────────

    public function test_guest_is_redirected_from_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_from_packages(): void
    {
        $this->get(route('packages.index'))->assertRedirect(route('login'));
    }
}
