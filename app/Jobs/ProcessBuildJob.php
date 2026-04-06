<?php

namespace App\Jobs;

use App\Enums\ArtifactType;
use App\Enums\BuildStatus;
use App\Events\BuildCompleted;
use App\Events\BuildFailed;
use App\Models\Build;
use App\Models\BuildArtifact;
use App\Services\BuildService;
use App\Services\HashService;
use App\Services\ManifestService;
use App\Services\VerificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessBuildJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    public function __construct(public Build $build)
    {
        $this->onQueue('builds');
    }

    public function backoff(): array
    {
        return [30, 60, 120];
    }

    public function handle(
        BuildService $buildService,
        VerificationService $verificationService,
        ManifestService $manifestService,
        HashService $hashService
    ): void {
        $this->build->update([
            'status' => BuildStatus::Processing,
            'started_at' => now(),
        ]);

        $version = $this->build->packageVersion;
        $results = $verificationService->verifyVersion($version);

        $invalidFiles = collect($results)->where('valid', false);
        if ($invalidFiles->isNotEmpty()) {
            $names = $invalidFiles->pluck('original_name')->implode(', ');
            $this->build->update([
                'status' => BuildStatus::Failed,
                'error_message' => "Verification failed for files: {$names}",
                'completed_at' => now(),
            ]);
            event(new BuildFailed($this->build));
            return;
        }

        $buildDir = $buildService->getBuildDirectory($this->build);

        $zipPath = $buildService->createArchive($this->build, $buildDir);
        $manifestPath = $manifestService->generateAndSave($this->build, $buildDir . '/manifest.json');

        $zipHash = $hashService->computeSha256($zipPath);
        $manifestHash = $hashService->computeSha256($manifestPath);

        $zipStoredPath = "builds/{$this->build->uuid}/package.zip";
        $manifestStoredPath = "builds/{$this->build->uuid}/manifest.json";

        BuildArtifact::create([
            'build_id' => $this->build->id,
            'type' => ArtifactType::Zip,
            'file_path' => $zipStoredPath,
            'file_size' => filesize($zipPath),
            'sha256' => $zipHash,
        ]);

        BuildArtifact::create([
            'build_id' => $this->build->id,
            'type' => ArtifactType::Manifest,
            'file_path' => $manifestStoredPath,
            'file_size' => filesize($manifestPath),
            'sha256' => $manifestHash,
        ]);

        // Check if this publisher's first build needs admin review
        $publisher = $this->build->user;
        if (!$publisher->publisher_reviewed && !$publisher->isAdmin() && !$publisher->isCreator()) {
            $publisher->update(['publisher_reviewed' => true]);
            $this->build->update([
                'status'       => BuildStatus::PendingReview,
                'completed_at' => now(),
            ]);
            return; // Await admin approval before marking completed
        }

        $this->build->update([
            'status'       => BuildStatus::Completed,
            'completed_at' => now(),
        ]);

        event(new BuildCompleted($this->build));
    }

    public function failed(\Throwable $exception): void
    {
        $this->build->update([
            'status' => BuildStatus::Failed,
            'error_message' => $exception->getMessage(),
            'completed_at' => now(),
        ]);

        event(new BuildFailed($this->build));
    }
}
