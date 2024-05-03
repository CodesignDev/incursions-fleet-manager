<?php

namespace App\Enums;

enum FleetInviteState: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case TIMED_OUT = 'timed_out';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
}
