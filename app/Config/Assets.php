<?php
/**
 * Assets Configuration.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 */

return array(

    /*
    |--------------------------------------------------------------------------
    | Compress Assets
    |--------------------------------------------------------------------------
    |
    | Whether or not the CSS and JS files are automatically compressed.
    |
    | Default: true
    |
    */

    'compress' => true,

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | The Browser Caching configuration.
    |
    */

    'cache' => array(
        'ttl'          => 600,
        'maxAge'       => 10800,
        'sharedMaxAge' => 600,
    ),

);
