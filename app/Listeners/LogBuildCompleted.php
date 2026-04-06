<?php

namespace App\Listeners;

use App\Events\BuildCompleted;
use App\Models\AuditLog;

class LogBuildCompleted
{
    public function handle(BuildCompleted $event): void
    {
        AuditLog::create([
            'user_id' => $event->build->user_id,
            'action' => 'build.completed',
            'auditable_type' => get_class($event->build),
            'auditable_id' => $event->build->id,
            'new_values' => [
                'uuid' => $event->build->uuid,
                'status' => 'completed',
            ],
        ]);
    }
}
