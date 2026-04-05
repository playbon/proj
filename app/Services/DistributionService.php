<?php

namespace App\Services;

use App\Models\BuildArtifact;
use App\Models\DownloadStat;
use App\Models\Package;
use App\Models\PackageVersion;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DistributionService
{
    public function publish(PackageVersion $version): void
    {
        $version->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function revoke(PackageVersion $version): void
    {
        $version->update([
            'is_revoked' => true,
        ]);
    }

    public function getLatestPublished(Package $package): ?PackageVersion
    {
        return $package->versions()
            ->published()
            ->notRevoked()
            ->latest('published_at')
            ->first();
    }

    public function download(BuildArtifact $artifact, ?User $user, string $ip): StreamedResponse
    {
        DownloadStat::create([
            'build_artifact_id' => $artifact->id,
            'user_id' => $user?->id,
            'ip_address' => $ip,
            'downloaded_at' => now(),
        ]);

        $fullPath = Storage::disk('local')->path($artifact->file_path);

        $version = $artifact->build->packageVersion;
        $packageSlug = $version->package->slug ?? 'package';
        $versionStr = $version->version ?? '0.0.0';
        $ext = pathinfo($artifact->file_path, PATHINFO_EXTENSION);
        $fileName = "{$packageSlug}-{$versionStr}.{$ext}";

        return response()->streamDownload(function () use ($fullPath) {
            readfile($fullPath);
        }, $fileName);
    }

    public function getDownloadStats(Package $package): array
    {
        $totalDownloads = 0;
        $versionStats = [];

        foreach ($package->versions as $version) {
            $count = 0;
            foreach ($version->builds as $build) {
                foreach ($build->artifacts as $artifact) {
                    $count += $artifact->downloadStats()->count();
                }
            }
            $versionStats[] = [
                'version' => $version->version,
                'downloads' => $count,
            ];
            $totalDownloads += $count;
        }

        return [
            'total' => $totalDownloads,
            'versions' => $versionStats,
        ];
    }
}
