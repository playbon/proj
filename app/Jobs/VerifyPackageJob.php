<?php

namespace App\Jobs;

use App\Events\PackageVerified;
use App\Models\AuditLog;
use App\Models\PackageVersion;
use App\Services\VerificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerifyPackageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(public PackageVersion $version)
    {
        $this->onQueue('verification');
    }

    public function handle(VerificationService $verificationService): void
    {
        $results = $verificationService->verifyVersion($this->version);
        $summary = $verificationService->getVerificationSummary($this->version->fresh(['files']));

        AuditLog::create([
            'action' => 'package.verified',
            'auditable_type' => PackageVersion::class,
            'auditable_id' => $this->version->id,
            'new_values' => $summary,
        ]);

        event(new PackageVerified($this->version, $results));
    }
}
