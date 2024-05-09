<?php

namespace App\Enums;

enum WaitlistRemovalReason: string
{
    case INVITED_TO_FLEET = 'invited_to_fleet';
    case REMOVED_FROM_WAITLIST = 'removed_from_waitlist';
    case SELF_REMOVED = 'self_removed';
    case UNKNOWN = 'unknown';
}
