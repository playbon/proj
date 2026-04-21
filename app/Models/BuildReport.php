<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuildReport extends Model
{
    protected $fillable = ['build_id', 'reporter_id', 'reason', 'status', 'reviewed_by'];

    public function build(): BelongsTo   { return $this->belongsTo(Build::class); }
    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reporter_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
