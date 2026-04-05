<?php

namespace App\Services;

use App\Models\Build;

class ManifestService
{
    public function __construct(protected HashService $hashService)
    {
    }

    public function generate(Build $build): array
    {
        $version = $build->packageVersion;
        $package = $version->package;
        $files = $version->files;

        $fileEntries = [];
        $totalSize = 0;

        foreach ($files as $file) {
            $fileEntries[] = [
                'path' => $file->original_name,
                'size' => $file->size,
                'sha256' => $file->sha256,
                'sha512' => $file->sha512,
            ];
            $totalSize += $file->size;
        }

        $manifest = [
            'schema_version' => '1.0',
            'package' => [
                'name' => $package->name,
                'version' => $version->version,
                'channel' => $package->channel->name,
                'description' => $package->description,
            ],
            'build' => [
                'id' => $build->uuid,
                'timestamp' => now()->toIso8601String(),
                'builder_version' => '1.0.0',
            ],
            'files' => $fileEntries,
            'integrity' => [
                'total_files' => count($fileEntries),
                'total_size' => $totalSize,
                'manifest_sha256' => '',
            ],
        ];

        $contentForHash = $manifest;
        unset($contentForHash['integrity']['manifest_sha256']);
        $manifest['integrity']['manifest_sha256'] = $this->hashService->computeFromContent(json_encode($contentForHash));

        return $manifest;
    }

    public function toJson(array $manifest): string
    {
        return json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function generateAndSave(Build $build, string $outputPath): string
    {
        $manifest = $this->generate($build);
        $json = $this->toJson($manifest);

        file_put_contents($outputPath, $json);

        return $outputPath;
    }
}
