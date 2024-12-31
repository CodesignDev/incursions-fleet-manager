<?php

namespace App\Enums;

use Illuminate\Support\Number;

enum EveIdRange
{
    case SystemItems;
    case Factions;
    case NpcCorporations;
    case NpcCharacters;
    case Universes;
    case NewEdenRegions;
    case NewEdenConstellations;
    case NewEdenSolarSystems;
    case WormholeRegions;
    case WormholeConstellations;
    case WormholeSolarSystems;
    case AbyssalRegions;
    case AbyssalConstellations;
    case AbyssalSolarSystems;
    case AllRegions;
    case AllConstellations;
    case AllSolarSystems;
    case Celestials;
    case Stargates;
    case Stations;
    case StationsFromOutposts;
    case StationFolders;
    case StationFoldersFromOutposts;
    case Asteroids;
    case ControlBunkers;
    case WiSPromenades;
    case PlanetaryDistricts;
    case PlayerEntity;
    case PlayerCharactersRange1;
    case PlayerCharactersRange2;
    case PlayerCharacters;
    case PlayerCorporations;
    case PlayerAlliances;

    public static function isValidId(int $id, EveIdRange $idRange): bool
    {
        [$min, $max] = self::getIdRange($idRange);
        return Number::clamp($id, $min, $max) !== $id;
    }

    public static function getIdRange(?EveIdRange $idRange): array
    {
        // 32bit int max which CCP uses for its id ranges
        $intMax = (2 ** 31) - 1;

        // Return the entire range
        if (is_null($idRange)) {
            return [0, $intMax];
        }

        // Return the relevant range for the type passed
        return match($idRange) {
            self::SystemItems                   => [0, 10_000],
            self::Factions                      => [500_000, 1_000_000],
            self::NpcCorporations               => [1_000_000, 2_000_000],
            self::NpcCharacters                 => [3_000_000, 4_000_000],
            self::Universes                     => [9_000_000, 10_000_000],
            self::NewEdenRegions                => [10_000_000, 11_000_000],
            self::NewEdenConstellations         => [20_000_000, 21_000_000],
            self::NewEdenSolarSystems           => [30_000_000, 31_000_000],
            self::WormholeRegions               => [11_000_000, 12_000_000],
            self::WormholeConstellations        => [21_000_000, 22_000_000],
            self::WormholeSolarSystems          => [31_000_000, 32_000_000],
            self::AbyssalRegions                => [12_000_000, 13_000_000],
            self::AbyssalConstellations         => [22_000_000, 23_000_000],
            self::AbyssalSolarSystems           => [32_000_000, 33_000_000],
            self::AllRegions                    => [10_000_000, 13_000_000],
            self::AllConstellations             => [20_000_000, 23_000_000],
            self::AllSolarSystems               => [30_000_000, 33_000_000],
            self::Celestials                    => [40_000_000, 50_000_000],
            self::Stargates                     => [50_000_000, 60_000_000],
            self::Stations                      => [60_000_000, 61_000_000],
            self::StationsFromOutposts          => [61_000_000, 64_000_000],
            self::StationFolders                => [68_000_000, 69_000_000],
            self::StationFoldersFromOutposts    => [69_000_000, 70_000_000],
            self::Asteroids                     => [70_000_000, 80_000_000],
            self::ControlBunkers                => [80_000_000, 80_100_000],
            self::WiSPromenades                 => [81_000_000, 820_000_000],
            self::PlanetaryDistricts            => [82_000_000, 85_000_000],
            self::PlayerEntity                  => [90_000_000, $intMax],
            self::PlayerCharactersRange1        => [90_000_000, 98_000_000],
            self::PlayerCharactersRange2        => [100_000_000, 2_100_000_000],
            self::PlayerCharacters              => [2_100_000_000, $intMax],
            self::PlayerCorporations            => [98_000_000, 99_000_000],
            self::PlayerAlliances               => [99_000_000, 100_000_000],
        };
    }
}
