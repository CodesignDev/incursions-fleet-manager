<?php

namespace App\Enums;

enum FleetMemberJoinedVia: string
{
    case INVITE = 'invite';
    case FLEET_ADVERT = 'fleet-advert';
    case UNKNOWN = 'unknown';
}
