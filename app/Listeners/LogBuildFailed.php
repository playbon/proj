<?php

namespace App\Listeners;

use App\Events\BuildFailed;
use App\Models\AuditLog;

class LogBuildFailed
{
    public function handle(BuildFailed $event): void
    {
        AuditLog::create([
            'user_id' => $event->build->user_id,
            'action' => 'build.failed',
            'auditable_type' => get_class($event->build),
            'auditable_id' => $event->build->id,
            'new_values' => [
                'uuid' => $event->build->uuid,
                'status' => 'failed',
                'error' => $event->build->error_message,
            ],
        ]);
    }
}
