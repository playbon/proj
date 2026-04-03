<?php

namespace App\Enums;

enum ChannelType: string
{
    case Stable = 'stable';
    case Beta = 'beta';
    case Nightly = 'nightly';
}
