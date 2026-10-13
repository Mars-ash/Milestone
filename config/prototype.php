<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Prototype Mode Status
    |--------------------------------------------------------------------------
    |
    | Prototype mode provides simulated fixtures and interactive UI for user
    | review. It is strictly restricted to local and testing environments and
    | MUST NEVER be accessible or active in production.
    |
    | When null, PrototypeManager defaults to checking app()->environment('local', 'testing').
    |
    */
    'enabled' => env('PROTOTYPE_ENABLED', null),

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | All prototype routes are isolated under this prefix.
    |
    */
    'prefix' => 'prototype',

    /*
    |--------------------------------------------------------------------------
    | Storage Driver for Prototype Simulation
    |--------------------------------------------------------------------------
    |
    | Uses session or array for fake store without touching business database.
    |
    */
    'storage' => env('PROTOTYPE_STORAGE', 'session'),
];
