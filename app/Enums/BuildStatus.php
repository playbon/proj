<?php

namespace App\Enums;

enum BuildStatus: string
{
    case Queued        = 'queued';
    case Processing    = 'processing';
    case Completed     = 'completed';
    case Failed        = 'failed';
    case PendingReview = 'pending_review';
    case Blocked       = 'blocked';
}
