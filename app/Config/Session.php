<?php
/**
 * Session Configuration.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 */


return array(
    'handler' => 'Mini\Session\FileSessionHandler', // The default Session Handler, using files for Session cache.

    // Storage configuration.
    'lifetime' => 180,                       // Number of minutes the Session is allowed to remain idle before it expires.
    'files'    => STORAGE_PATH .'sessions',  // File Session Handler - the location where the Session files may be stored.
    'lottery'  => array(2, 100),             // Option used by the Garbage Collector, to remove the stalled Session files.

    // Cookie configuration.
    'cookie'   => PREFIX .'session',
    'path'     => '/',
    'domain'   => null,
    'secure'   => false,

    // Wheter or not will be used the Cookies encryption.
    'encrypt'  => true
);
