<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class FleetLink
{
    /**
     * The regex to validate a ESI fleet link.
     */
    public const ESI_FLEET_LINK_REGEX = '/^https:\/\/esi\.evetech\.net\/(?:v1|dev|latest|legacy)\/fleets\/(\d+)\/\?.*/i';

    /**
     * Extrac the fleet id from a valid ESI based fleet link.
     */
    public static function extractFleetIdFromLink(string $link): int
    {
        if (! self::isFleetLink($link)) {
            return 0;
        }

        return str($link)
            ->match(self::ESI_FLEET_LINK_REGEX)
            ->toInteger();
    }

    /**
     * Verify if a link is a valid ESI fleet link.
     */
    public static function isFleetLink(string $link): bool
    {
        return Str::isMatch(self::ESI_FLEET_LINK_REGEX, $link);
    }

    /**
     * The validation rules required to validate a fleet link.
     */
    public static function validationRules(): array
    {
        return [
            'url',
            'regex:'.self::ESI_FLEET_LINK_REGEX,
        ];
    }
}
