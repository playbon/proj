<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageFile extends Model
{
    protected $fillable = [
        'package_version_id',
        'original_name',
        'stored_path',
        'mime_type',
        'size',
        'sha256',
        'sha512',
        'verification_status',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verification_status' => VerificationStatus::class,
            'verified_at' => 'datetime',
            'size' => 'integer',
        ];
    }

    public function packageVersion(): BelongsTo
    {
        return $this->belongsTo(PackageVersion::class);
    }
}
