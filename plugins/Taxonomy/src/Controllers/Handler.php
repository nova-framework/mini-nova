<?php

namespace Taxonomy\Controllers;

use Mini\Support\Facades\View;

use App\Controllers\BaseController;

use Taxonomy\Models\Vocabulary;


class Handler extends BaseController
{

    protected function show($vocabulary, $slug = null)
    {
		$vocabulary = Vocabulary::where('slug', $vocabulary)->first();

		$content = '<p>' .__d('taxonomy', 'Nothing here, yet!') .'</p>';

		$content .= '<pre>' .var_export($slug, true) .'</pre>';

		return View::make('Default')
			->shares('title', $vocabulary->name)
			->with('content', $content);
    }
}
