<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\PackageVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageVersionFactory extends Factory
{
    protected $model = PackageVersion::class;

    public function definition(): array
    {
        return [
            'package_id'   => Package::factory(),
            'version'      => $this->faker->semver(),
            'changelog'    => $this->faker->sentence(),
            'is_published' => false,
            'is_revoked'   => false,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state([
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function revoked(): static
    {
        return $this->state(['is_revoked' => true]);
    }
}
