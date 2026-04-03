<?php

namespace App\Enums;

enum ArtifactType: string
{
    case Zip = 'zip';
    case Manifest = 'manifest';
}
