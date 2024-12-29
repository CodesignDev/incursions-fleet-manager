<?php

namespace App\Exceptions;

use App\Enums\ExpectedEveIdRange;
use Exception;

class InvalidEveIdRange extends Exception
{
    /**
     * The ID that was encountered.
     */
    public int $entityId;

    /**
     * The minimum expected ID range.
     */
    public int $minRange;

    /**
     * The maximum expected ID range.
     */
    public int $maxRange;

    /**
     * Set the invalid ID and the type of entity ID range that was expected.
     */
    public function withId(int $entityId, ExpectedEveIdRange $expectedRange = null): static
    {
        $this->entityId = $entityId;

        [$minRange, $maxRange] = $this->deriveIdRange($expectedRange);
        $this->minRange = $minRange;
        $this->maxRange = $maxRange;

        $this->message = "An unexpected ID was encountered: $entityId";

        if (! is_null($expectedRange)) {
            $this->message .= '. Expected ID range was '.implode(' - ', [$minRange, $maxRange]);
        } else {
            $this->message .= '.';
        }

        return $this;
    }

    /**
     * Get the ID that is out of range.
     *
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * Get the expected ID range
     *
     * @return int[]
     */
    public function getExpectedIdRange(): array
    {
        return [$this->minRange, $this->maxRange];
    }

    /**
     * @return int[]
     */
    private function deriveIdRange(?ExpectedEveIdRange $expectedRange): array
    {
        // 32bit int max which CCP uses for its id ranges
        $intMax = (2 ** 31) - 1;

        // Return the entire range
        if (is_null($expectedRange)) {
            return [0, $intMax];
        }

        // Return the relevant range for the type passed
        return match($expectedRange) {
            ExpectedEveIdRange::SystemItems => [0, 10_000],
            ExpectedEveIdRange::Factions => [500_000, 1_000_000],
            ExpectedEveIdRange::NpcCorporations => [1_000_000, 2_000_000],
            ExpectedEveIdRange::NpcCharacters => [3_000_000, 4_000_000],
            ExpectedEveIdRange::Universes => [9_000_000, 10_000_000],
            ExpectedEveIdRange::NewEdenRegions => [10_000_000, 11_000_000],
            ExpectedEveIdRange::NewEdenConstellations => [20_000_000, 21_000_000],
            ExpectedEveIdRange::NewEdenSolarSystems => [30_000_000, 31_000_000],
            ExpectedEveIdRange::WormholeRegions => [11_000_000, 12_000_000],
            ExpectedEveIdRange::WormholeConstellations => [21_000_000, 22_000_000],
            ExpectedEveIdRange::WormholeSolarSystems => [31_000_000, 32_000_000],
            ExpectedEveIdRange::AbyssalRegions => [12_000_000, 13_000_000],
            ExpectedEveIdRange::AbyssalConstellations => [22_000_000, 23_000_000],
            ExpectedEveIdRange::AbyssalSolarSystems => [32_000_000, 33_000_000],
            ExpectedEveIdRange::AllRegions => [10_000_000, 13_000_000],
            ExpectedEveIdRange::AllConstellations => [20_000_000, 23_000_000],
            ExpectedEveIdRange::AllSolarSystems => [30_000_000, 33_000_000],
            ExpectedEveIdRange::Celestials => [40_000_000, 50_000_000],
            ExpectedEveIdRange::Stargates => [50_000_000, 60_000_000],
            ExpectedEveIdRange::Stations => [60_000_000, 61_000_000],
            ExpectedEveIdRange::StationsFromOutposts => [61_000_000, 64_000_000],
            ExpectedEveIdRange::StationFolders => [68_000_000, 69_000_000],
            ExpectedEveIdRange::StationFoldersFromOutposts => [69_000_000, 70_000_000],
            ExpectedEveIdRange::Asteroids => [70_000_000, 80_000_000],
            ExpectedEveIdRange::ControlBunkers => [80_000_000, 80_100_000],
            ExpectedEveIdRange::WiSPromenades => [81_000_000, 820_000_000],
            ExpectedEveIdRange::PlanetaryDistricts => [82_000_000, 85_000_000],
            ExpectedEveIdRange::PlayerEntity => [90_000_000, $intMax],
            ExpectedEveIdRange::PlayerCharactersRange1 => [90_000_000, 98_000_000],
            ExpectedEveIdRange::PlayerCharactersRange2 => [100_000_000, 2_100_000_000],
            ExpectedEveIdRange::PlayerCharacters => [2_100_000_000, $intMax],
            ExpectedEveIdRange::PlayerCorporations => [98_000_000, 99_000_000],
            ExpectedEveIdRange::PlayerAlliances => [99_000_000, 100_000_000],
        };
    }
}
