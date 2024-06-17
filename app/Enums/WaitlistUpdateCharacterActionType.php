<?php

namespace App\Enums;

enum WaitlistUpdateCharacterActionType: string
{
    case ADD = 'add';
    case UPDATE = 'update';
    case REMOVE = 'remove';
    case UNKNOWN = 'unknown';
}
