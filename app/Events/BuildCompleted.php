<?php

namespace App\Events;

use App\Models\Build;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BuildCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public Build $build)
    {
    }
}
