<?php

namespace App\Services;

use App\Enums\BuildStatus;
use App\Jobs\ProcessBuildJob;
use App\Models\Build;
use App\Models\PackageVersion;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class BuildService
{
    public function initiate(PackageVersion $version, User $user): Build
    {
        $build = Build::create([
            'uuid' => Str::uuid()->toString(),
            'package_version_id' => $version->id,
            'user_id' => $user->id,
            'status' => BuildStatus::Queued,
        ]);

        ProcessBuildJob::dispatch($build);

        return $build;
    }

    public function createArchive(Build $build, string $outputDir): string
    {
        $version = $build->packageVersion;
        $files = $version->files;

        $zipPath = $outputDir . '/package.zip';

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($files as $file) {
            $fullPath = Storage::disk('local')->path($file->stored_path);
            if (file_exists($fullPath)) {
                $zip->addFile($fullPath, $file->original_name);
            }
        }

        $zip->close();

        return $zipPath;
    }

    public function getBuildDirectory(Build $build): string
    {
        $path = Storage::disk('local')->path("builds/{$build->uuid}");

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }

    public function cleanup(Build $build): void
    {
        Storage::disk('local')->deleteDirectory("builds/{$build->uuid}");
    }
}
