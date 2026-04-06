<?php

namespace App\Listeners;

use App\Events\PackageVerified;
use App\Models\AuditLog;

class LogPackageVerified
{
    public function handle(PackageVerified $event): void
    {
        AuditLog::create([
            'action' => 'package.verified',
            'auditable_type' => get_class($event->version),
            'auditable_id' => $event->version->id,
            'new_values' => $event->results,
        ]);
    }
}
