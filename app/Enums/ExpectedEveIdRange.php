<?php

namespace App\Enums;

enum ExpectedEveIdRange
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
}
