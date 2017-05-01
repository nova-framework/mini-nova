<?php
/**
 * Application Configuration.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 */


return array(

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => true,

    /*
    |--------------------------------------------------------------------------
    | Base Site URL
    |--------------------------------------------------------------------------
    |
    | URL to your Nova root. Typically this will be your base URL,
    | WITH a trailing slash:
    |
    |   http://example.com/
    |
    | WARNING: You MUST set this value!
    |
    */

    'url' => 'http://www.mini-me.dev/',

   /*
    |--------------------------------------------------------------------------
    | The Administrator's E-mail Address
    |--------------------------------------------------------------------------
    |
    | The e-mail address for your application's administrator.
    |
    */

    'email' => 'admin@mini-me.dev',

    /*
    |--------------------------------------------------------------------------
    | The Website Path
    |--------------------------------------------------------------------------
    |
    */

    'path' => '/',

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application.
    |
    */

    'name' => 'Mini-me 1.0',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    | http://www.php.net/manual/en/timezones.php
    |
    */

    'timezone' => 'Europe/Bucharest',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    | This page can be used to generate key - http://novaframework.com/token-generator
    |
    */

    'key' => 'SomeRandomStringThere_1234567890',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => array(
        // The Framework Providers.
        'Mini\Routing\RoutingServiceProvider',
        'Mini\Log\LogServiceProvider',
        'Mini\Cookie\CookieServiceProvider',
        'Mini\View\ViewServiceProvider',


        // The Application Providers.
    ),

    /*
    |--------------------------------------------------------------------------
    | Service Provider Manifest
    |--------------------------------------------------------------------------
    |
    | The service provider manifest is used by Nova to lazy load service
    | providers which are not needed for each request, as well to keep a
    | list of all of the services. Here, you may set its storage spot.
    |
    */

    'manifest' => STORAGE_PATH,

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => array(
        // The Support Classes.
        'Arr'           => 'Mini\Support\Arr',
        'Str'           => 'Mini\Support\Str',

        // The Support Facades.
        'App'           => 'Mini\Support\Facades\App',
        'Cookie'        => 'Mini\Support\Facades\Cookie',
        'Config'        => 'Mini\Support\Facades\Config',
        'Event'         => 'Mini\Support\Facades\Event',
        'Input'         => 'Mini\Support\Facades\Input',
        'Log'           => 'Mini\Support\Facades\Log',
        'Request'       => 'Mini\Support\Facades\Request',
        'Response'      => 'Mini\Support\Facades\Response',
        'Route'         => 'Mini\Support\Facades\Route',
        'View'          => 'Mini\Support\Facades\View',
    ),
);
