<?php

namespace App\Listeners;

use App\Events\PackageDownloaded;
use App\Models\AuditLog;

class RecordDownloadStat
{
    public function handle(PackageDownloaded $event): void
    {
        AuditLog::create([
            'user_id' => $event->user?->id,
            'action' => 'artifact.downloaded',
            'auditable_type' => get_class($event->artifact),
            'auditable_id' => $event->artifact->id,
        ]);
    }
}
