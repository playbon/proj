<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DownloadStat extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'build_artifact_id',
        'user_id',
        'ip_address',
        'downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'downloaded_at' => 'datetime',
        ];
    }

    public function buildArtifact(): BelongsTo
    {
        return $this->belongsTo(BuildArtifact::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
