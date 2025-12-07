<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Duration
    |--------------------------------------------------------------------------
    |
    | The default duration (in seconds) for caching block data.
    | Default is 24 hours (86400 seconds).
    |
    */
    'cache_duration' => 60 * 60 * 24,

    /*
    |--------------------------------------------------------------------------
    | Dispatch Events
    |--------------------------------------------------------------------------
    |
    | Whether to dispatch events when users block/unblock each other.
    | Set to false to disable event dispatching.
    |
    */
    'dispatch_events' => true,

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | The user model class to use for relationships.
    | Falls back to auth config if not specified.
    |
    */
    'user_model' => null,
];
