<?php

namespace App\Enums;

enum UserRole: string
{
    case Creator   = 'creator';
    case Admin     = 'admin';
    case Publisher = 'publisher';
    case Viewer    = 'viewer';
    case Ghost     = 'ghost';
}
