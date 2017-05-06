<?php
/**
 * Cache configuration
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 */


return array(

    /*
    |--------------------------------------------------------------------------
    | Default Cache Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache "driver" that will be used when
    | using the Caching library. Of course, you may use other drivers any
    | time you wish. This is the default when another is not specified.
    |
    | Supported: "file"
    */

    'driver' => 'file',

    /*
    |--------------------------------------------------------------------------
    | File Cache Location
    |--------------------------------------------------------------------------
    |
    | When using the "file" cache driver, we need a location where the cache
    | files may be stored. A sensible default has been specified, but you
    | are free to change it to any other place on disk that you desire.
    */

    'path' => STORAGE_PATH .'cache',

    /*
    |--------------------------------------------------------------------------
    | Cache Key Prefix
    |--------------------------------------------------------------------------
    |
    | When utilizing a RAM based store such as APC or Memcached, there might
    | be other applications utilizing the same cache. So, we'll specify a
    | value to get prefixed to all our keys so we can avoid collisions.
    */

    'prefix' => 'nova',

);
