<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PackageVersion extends Model
{
    use HasFactory;
    protected $fillable = [
        'package_id',
        'version',
        'changelog',
        'is_published',
        'is_revoked',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_revoked' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(PackageFile::class);
    }

    public function builds(): HasMany
    {
        return $this->hasMany(Build::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeNotRevoked(Builder $query): Builder
    {
        return $query->where('is_revoked', false);
    }
}
