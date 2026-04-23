<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function creator(): static
    {
        return $this->state(['role' => \App\Enums\UserRole::Creator]);
    }

    public function admin(): static
    {
        return $this->state(['role' => \App\Enums\UserRole::Admin]);
    }

    public function publisher(): static
    {
        return $this->state(['role' => \App\Enums\UserRole::Publisher]);
    }

    public function viewer(): static
    {
        return $this->state(['role' => \App\Enums\UserRole::Viewer]);
    }

    public function ghost(): static
    {
        return $this->state(['role' => \App\Enums\UserRole::Ghost]);
    }

    public function banned(): static
    {
        return $this->state([
            'banned_until' => now()->addDays(30),
            'ban_count'    => 1,
        ]);
    }
}
