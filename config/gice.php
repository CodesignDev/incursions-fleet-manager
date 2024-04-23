<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GICE
    |--------------------------------------------------------------------------
    |
    | This file is for storing the required settings used when communicating
    | with GICE.
    |
    */

    // GICE Server details
    'host'   => env('GICE_HOST', 'gice.goonfleet.com'),
    'port'   => env('GICE_PORT', 443),
    'scheme' => env('GICE_SCHEME', 'https'),

    // GICE Auth application details
    'client_id'     => env('GICE_CLIENT_ID', ''),
    'client_secret' => env('GICE_CLIENT_SECRET', ''),
    'redirect_uri'  => env('GICE_REDIRECT_URI', ''),

];
