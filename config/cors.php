<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins
    |--------------------------------------------------------------------------
    |
    | Specify which origins are allowed to access your application. You can
    | use `*` to allow all origins or list them individually.
    |
    */

    'allowed_origins' => [
        // Add your Flutter web development server's origin(s) here
        // Using http://localhost:* and http://127.0.0.1:* is usually fine for local dev
        // as the port Flutter web uses can change.
        'http://localhost:*',
        'http://127.0.0.1:*',
        // If you know the exact port from your Flutter web logs (e.g., 56269),
        // you can add it specifically:
        // 'http://127.0.0.1:56269',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed Origins Patterns
    |--------------------------------------------------------------------------
    |
    | You can also specify origins by defining regex patterns.
    |
    */

    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Methods
    |--------------------------------------------------------------------------
    |
    | Specify which HTTP methods are allowed. `*` allows all methods.
    |
    */

    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers
    |--------------------------------------------------------------------------
    |
    | Specify which HTTP headers are allowed in requests. `*` allows all.
    |
    */

    'allowed_headers' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Exposed Headers
    |--------------------------------------------------------------------------
    |
    | Specify which headers should be exposed to the browser.
    |
    */

    'exposed_headers' => [],

    /*
    |--------------------------------------------------------------------------
    | Max Age
    |--------------------------------------------------------------------------
    |
    | Specifies how long the results of a preflight request can be cached.
    |
    */

    'max_age' => 0,

    /*
    |--------------------------------------------------------------------------
    | Supports Credentials
    |--------------------------------------------------------------------------
    |
    | Indicates whether or not the actual request can be made using credentials.
    | Set to true if you are using session-based authentication (like Sanctum with cookies).
    */

    'supports_credentials' => false, // Change to true if using Sanctum with cookies for SPA auth

    /*
    |--------------------------------------------------------------------------
    | Paths
    |--------------------------------------------------------------------------
    |
    | Specify which paths CORS should be applied to.
    | Typically, you'll want this for your API routes.
    | The default paths for API routes in Laravel are usually fine.
    */

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie' // If you use Laravel Sanctum for SPA authentication
    ],

]; 