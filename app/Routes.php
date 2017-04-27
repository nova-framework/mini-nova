<?php

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
        'id' => '([0-9]+)',
    ),
));
