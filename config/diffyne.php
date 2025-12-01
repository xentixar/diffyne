<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Transport Mode
    |--------------------------------------------------------------------------
    |
    | Diffyne supports two transport modes:
    | - 'ajax': Standard HTTP requests (default, production-ready)
    | - 'websocket': Real-time WebSocket connections (experimental)
    |
    */

    'transport' => env('DIFFYNE_TRANSPORT', 'ajax'),

    /*
    |--------------------------------------------------------------------------
    | WebSocket Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WebSocket server when using 'websocket' transport mode.
    |
    */

    'websocket' => [
        'host' => env('DIFFYNE_WS_HOST', '0.0.0.0'),
        'port' => env('DIFFYNE_WS_PORT', 8080),
        'path' => env('DIFFYNE_WS_PATH', '/diffyne'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Component Namespace
    |--------------------------------------------------------------------------
    |
    | Default namespace for generating Diffyne components.
    |
    */

    'component_namespace' => 'App\\Diffyne',

    /*
    |--------------------------------------------------------------------------
    | View Path
    |--------------------------------------------------------------------------
    |
    | Default path for Diffyne component views.
    |
    */

    'view_path' => resource_path('views/diffyne'),

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix for Diffyne's internal routes.
    |
    */

    'route_prefix' => '_diffyne',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware applied to Diffyne routes.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Asset URL
    |--------------------------------------------------------------------------
    |
    | URL path for Diffyne's JavaScript assets.
    |
    */

    'asset_url' => '/vendor/diffyne',

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | Enable verbose logging and debugging information.
    |
    */

    'debug' => env('DIFFYNE_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Performance Options
    |--------------------------------------------------------------------------
    |
    | Fine-tune performance and caching behavior.
    |
    */

    'performance' => [
        'cache_rendered_views' => env('DIFFYNE_CACHE_VIEWS', true),
        'minify_patches' => env('DIFFYNE_MINIFY_PATCHES', true),
        'debounce_default' => 150, // milliseconds
    ],

];
