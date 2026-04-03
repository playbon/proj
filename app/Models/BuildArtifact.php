<?php

namespace App\Models;

use App\Enums\ArtifactType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BuildArtifact extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'build_id',
        'type',
        'file_path',
        'file_size',
        'sha256',
    ];

    protected function casts(): array
    {
        return [
            'type' => ArtifactType::class,
            'file_size' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (BuildArtifact $artifact) {
            $artifact->created_at = now();
        });
    }

    public function build(): BelongsTo
    {
        return $this->belongsTo(Build::class);
    }

    public function downloadStats(): HasMany
    {
        return $this->hasMany(DownloadStat::class);
    }
}
