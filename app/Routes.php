<?php

use Mini\Http\Request;


//
// General patterns for the route parameters.

//$router->pattern('slug', '(.*)');


//
// The routes definition.

// The static Pages.
$router->get('/', 'Pages@display');

$router->get('pages/{slug}', 'Pages@display')->where('slug', '(.*)');

// The Language Changer.
$router->get('language/{language}', function (Request $request, $language)
{
    $url = Config::get('app.url');

    $languages = Config::get('languages');

    if (array_key_exists($language, $languages) && Str::startsWith($request->header('referer'), $url)) {
        Session::set('language', $language);

        // Store also the current Language in a Cookie lasting five years.
        Cookie::queue(PREFIX .'language', $language, Cookie::FIVEYEARS);
    }

    return Redirect::back();

})->where('language', '([a-z]{2})');

/*
// A Catch-All route.
$router->fallback(function($slug)
{
    $content = '<pre>' .var_export($slug, true) .'</pre>';

    $view = View::make('Default')
        ->shares('title', 'Catch-All Route')
        ->with('content', $content);

    return View::make('Layouts/Default')->with('content', $view);
});
*/

