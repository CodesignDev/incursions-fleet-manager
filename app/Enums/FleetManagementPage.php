<?php

namespace App\Enums;

enum FleetManagementPage: string
{
    case WAITLIST = 'waitlist';
    case FLEET_MEMBERS = 'members';
    case FLEET_SETTINGS = 'settings';
}
