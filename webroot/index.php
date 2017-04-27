<?php

defined('DS') || define('DS', DIRECTORY_SEPARATOR);

//--------------------------------------------------------------------------
// Define the absolute paths for Application directories
//--------------------------------------------------------------------------

define('BASEPATH', realpath(__DIR__ .'/../') .DS);

define('WEBPATH', realpath(__DIR__) .DS);

define('APPPATH', BASEPATH .'app' .DS);

//--------------------------------------------------------------------------
// Load the Composer Autoloader
//--------------------------------------------------------------------------

require BASEPATH .'vendor/autoload.php';

//--------------------------------------------------------------------------
// Run the Application
//--------------------------------------------------------------------------

use Mini\Http\Request;
use Mini\Routing\Router;


// Create the Router instance.
$router = new Router();

// Additional patterns for routes.
$router->pattern('slug', '(.*)');

// The routes definition.
$router->any('/', function()
{
    return "Homepage";
});

$router->get('sample/{name?}/{slug?}', 'App\Controllers\Sample@index');

$router->post('sample', 'App\Controllers\Sample@store');


$router->get('test/{id?}/{name?}/{slug?}', array('uses' => function($id, $name = null, $slug = null)
{
    return array('id' => $id, 'name' => $name, 'slug' => $slug);

}, 'where' => array(
        'id' => '[0-9]+',
    ),
));

// Create the Request instance.
$request = Request::createFromGlobals();

// Dispatch the Request instance via Router.
$response = $router->dispatch($request);

// Send the Response instance returned by dispatching.
$response->send();
