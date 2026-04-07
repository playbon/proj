<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'last_seen_at',
        'banned_until',
        'ban_count',
        'publisher_reviewed',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'role'               => UserRole::class,
            'last_seen_at'       => 'datetime',
            'banned_until'       => 'datetime',
            'publisher_reviewed' => 'boolean',
        ];
    }

    // ── Role helpers ────────────────────────────────────────

    public function isCreator(): bool
    {
        return $this->role === UserRole::Creator;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [UserRole::Creator, UserRole::Admin]);
    }

    public function isGhost(): bool
    {
        return $this->role === UserRole::Ghost;
    }

    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(2));
    }

    public function isBanned(): bool
    {
        return $this->banned_until && $this->banned_until->isFuture();
    }

    public function ban(): void
    {
        $count = $this->ban_count + 1;
        $days  = match (true) {
            $count === 1 => 30,
            $count === 2 => 90,
            default      => 36500, // permanent (~100 years)
        };

        $this->update([
            'banned_until' => now()->addDays($days),
            'ban_count'    => $count,
        ]);
    }

    // ── Relationships ────────────────────────────────────────

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function builds(): HasMany
    {
        return $this->hasMany(Build::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function verificationRequest(): HasOne
    {
        return $this->hasOne(VerificationRequest::class);
    }
}
