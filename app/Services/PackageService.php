<?php

namespace App\Services;

use App\Models\Package;
use App\Models\PackageFile;
use App\Models\PackageVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PackageService
{
    public function __construct(protected HashService $hashService)
    {
    }

    public function create(User $user, array $data): Package
    {
        $data['user_id'] = $user->id;
        $data['slug'] = Str::slug($data['name']);

        return Package::create($data);
    }

    public function update(Package $package, array $data): Package
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $package->update($data);

        return $package->fresh();
    }

    public function delete(Package $package): void
    {
        foreach ($package->versions as $version) {
            $storagePath = "packages/{$package->slug}/{$version->version}";
            Storage::disk('local')->deleteDirectory($storagePath);
        }

        $package->delete();
    }

    public function createVersion(Package $package, array $data): PackageVersion
    {
        $data['package_id'] = $package->id;

        $version = PackageVersion::create($data);

        // Copy files from the most recent previous version
        $previous = $package->versions()
            ->where('id', '!=', $version->id)
            ->latest()
            ->first();

        if ($previous && $previous->files->isNotEmpty()) {
            $newPath = "packages/{$package->slug}/{$version->version}";

            foreach ($previous->files as $file) {
                $newStoredPath = $newPath . '/' . $file->original_name;

                if (Storage::disk('local')->exists($file->stored_path)) {
                    Storage::disk('local')->copy($file->stored_path, $newStoredPath);

                    PackageFile::create([
                        'package_version_id' => $version->id,
                        'original_name'      => $file->original_name,
                        'stored_path'        => $newStoredPath,
                        'mime_type'          => $file->mime_type,
                        'size'               => $file->size,
                        'sha256'             => $file->sha256,
                        'sha512'             => $file->sha512,
                        'verification_status' => 'valid',
                    ]);
                }
            }

            \App\Jobs\VerifyPackageJob::dispatch($version);
        }

        return $version;
    }

    public function publishVersion(PackageVersion $version): void
    {
        $version->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function revokeVersion(PackageVersion $version): void
    {
        $version->update([
            'is_revoked' => true,
        ]);
    }

    public function uploadFiles(PackageVersion $version, array $files): Collection
    {
        $package = $version->package;
        $storagePath = "packages/{$package->slug}/{$version->version}";
        $uploadedFiles = collect();

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $storedPath = $file->storeAs($storagePath, $originalName, 'local');
            $fullPath = Storage::disk('local')->path($storedPath);

            $packageFile = PackageFile::create([
                'package_version_id' => $version->id,
                'original_name' => $originalName,
                'stored_path' => $storedPath,
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize(),
                'sha256' => $this->hashService->computeSha256($fullPath),
                'sha512' => $this->hashService->computeSha512($fullPath),
                'verification_status' => 'pending',
            ]);

            $uploadedFiles->push($packageFile);
        }

        if ($uploadedFiles->isNotEmpty()) {
            \App\Jobs\VerifyPackageJob::dispatch($version);
        }

        return $uploadedFiles;
    }
}
