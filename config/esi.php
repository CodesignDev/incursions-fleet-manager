<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ESI
    |--------------------------------------------------------------------------
    |
    | This file is for storing the settings used for communicating with ESI.
    |
    */

    // ESI Server details
    'host'   => env('ESI_HOST', 'esi.evetech.net'),
    'port'   => env('ESI_PORT', 443),
    'scheme' => env('ESI_SCHEME', 'https'),

    // ESI Proxy Server details
    'proxy_host'   => env('ESI_PROXY_HOST', ''),
    'proxy_port'   => env('ESI_PROXY_PORT', ''),
    'proxy_scheme' => env('ESI_PROXY_SCHEME', ''),
    'proxy_path'   => env('ESI_PROXY_BASE_PATH', ''),
    'proxy_token'  => env('ESI_PROXY_TOKEN', ''),

    // ESI Version to use (if not specified)
    'version' => env('ESI_DEFAULT_VERSION', 'latest'),

    // User agent that is presented to ESI
    'user_agent'         => env('ESI_USER_AGENT', 'Incursion Esi App/1.0'),
    'user_agent_contact' => env('ESI_USER_AGENT_CONTACT', ''),

    // ESI Auth application details
    'client_id'     => env('ESI_CLIENT_ID', ''),
    'client_secret' => env('ESI_CLIENT_SECRET', ''),
    'redirect_uri'  => env('ESI_REDIRECT_URI', ''),

];
