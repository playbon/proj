<?php

namespace App\Events;

use App\Models\BuildArtifact;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PackageDownloaded
{
    use Dispatchable, SerializesModels;

    public function __construct(public BuildArtifact $artifact, public ?User $user)
    {
    }
}
