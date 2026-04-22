<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageReport extends Model
{
    protected $fillable = ['package_id', 'reporter_id', 'reason', 'status', 'reviewed_by'];

    public function package(): BelongsTo  { return $this->belongsTo(Package::class); }
    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reporter_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
