<?php

namespace App\Events;

use App\Models\PackageVersion;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PackageVerified
{
    use Dispatchable, SerializesModels;

    public function __construct(public PackageVersion $version, public array $results)
    {
    }
}
