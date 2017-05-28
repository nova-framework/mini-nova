<?php

namespace Taxonomy\Controllers;

use Mini\Support\Facades\View;

use App\Controllers\BaseController;

use Taxonomy\Models\Vocabulary;


class Handler extends BaseController
{

    protected function show($slug, $term = null)
    {
		$vocabulary = Vocabulary::where('slug', $slug)->first();

		$content = '<h3>' .__d('taxonomy', 'Nothing here, yet!') .'</h3><br>';

		$content .= '<pre>' .var_export($slug, true) .'</pre>';
		$content .= '<pre>' .var_export($term, true) .'</pre>';

		return View::make('Default')
			->shares('title', $vocabulary->name)
			->with('content', $content);
    }
}
