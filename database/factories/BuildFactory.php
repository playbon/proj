<?php

namespace Database\Factories;

use App\Enums\BuildStatus;
use App\Models\Build;
use App\Models\PackageVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BuildFactory extends Factory
{
    protected $model = Build::class;

    public function definition(): array
    {
        return [
            'uuid'               => (string) Str::uuid(),
            'package_version_id' => PackageVersion::factory(),
            'user_id'            => User::factory()->publisher(),
            'status'             => BuildStatus::Queued,
            'error_message'      => null,
            'started_at'         => null,
            'completed_at'       => null,
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status'       => BuildStatus::Completed,
            'started_at'   => now()->subSeconds(5),
            'completed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state([
            'status'        => BuildStatus::Failed,
            'started_at'    => now()->subSeconds(2),
            'completed_at'  => now(),
            'error_message' => 'Build failed: verification error',
        ]);
    }

    public function blocked(): static
    {
        return $this->state(['status' => BuildStatus::Blocked]);
    }

    public function pendingReview(): static
    {
        return $this->state(['status' => BuildStatus::PendingReview]);
    }
}
