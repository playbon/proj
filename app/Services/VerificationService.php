<?php

namespace App\Services;

use App\Enums\VerificationStatus;
use App\Models\PackageFile;
use App\Models\PackageVersion;
use Illuminate\Support\Facades\Storage;

class VerificationService
{
    public function __construct(protected HashService $hashService)
    {
    }

    public function verifyFile(PackageFile $file): bool
    {
        $fullPath = Storage::disk('local')->path($file->stored_path);

        if (!file_exists($fullPath)) {
            $file->update([
                'verification_status' => VerificationStatus::Invalid,
                'verified_at' => now(),
            ]);
            return false;
        }

        $computedHash = $this->hashService->computeSha256($fullPath);
        $isValid = $computedHash === $file->sha256;

        $file->update([
            'verification_status' => $isValid ? VerificationStatus::Valid : VerificationStatus::Invalid,
            'verified_at' => now(),
        ]);

        return $isValid;
    }

    public function verifyVersion(PackageVersion $version): array
    {
        $results = [];

        foreach ($version->files as $file) {
            $results[] = [
                'file_id' => $file->id,
                'original_name' => $file->original_name,
                'valid' => $this->verifyFile($file),
            ];
        }

        return $results;
    }

    public function getVerificationSummary(PackageVersion $version): array
    {
        $files = $version->files;

        return [
            'total' => $files->count(),
            'valid' => $files->where('verification_status', VerificationStatus::Valid)->count(),
            'invalid' => $files->where('verification_status', VerificationStatus::Invalid)->count(),
            'pending' => $files->where('verification_status', VerificationStatus::Pending)->count(),
        ];
    }
}
