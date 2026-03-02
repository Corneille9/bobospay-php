<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bobospay API Credentials
    |--------------------------------------------------------------------------
    |
    | Your Bobospay merchant application credentials. The client_id prefix
    | determines the environment automatically:
    |
    |   ci_test_* -> sandbox (sandbox.bobospay.com)
    |   ci_live_* -> production (bobospay.com)
    |
    | The client_secret is used as a Bearer token -- keep it confidential.
    |
    */

    'client_id' => env('BOBOSPAY_CLIENT_ID', ''),

    'client_secret' => env('BOBOSPAY_CLIENT_SECRET', ''),


    /*
    |--------------------------------------------------------------------------
    | HTTP Settings
    |--------------------------------------------------------------------------
    */

    'timeout' => env('BOBOSPAY_TIMEOUT', 30),

    'verify_ssl' => env('BOBOSPAY_VERIFY_SSL', true),
];

