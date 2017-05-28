<?php

namespace Taxonomy\Controllers;

use Mini\Database\ORM\ModelNotFoundException;
use Mini\Support\Facades\View;

use App\Controllers\BaseController;

use Taxonomy\Models\Term;
use Taxonomy\Models\Vocabulary;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class Handler extends BaseController
{

    public function handle($vocabulary, $slug = null)
    {
		try {
			$vocabulary = Vocabulary::where('slug', $vocabulary)->firstOrFail();
		}
		catch (ModelNotFoundException $e) {
			throw new NotFoundHttpException();
		}

		if (! is_null($slug)) {
			return $this->handleTerm($vocabulary, $slug);
		}

		return $this->handleVocabulary($vocabulary);
    }

	protected function handleVocabulary(Vocabulary $vocabulary)
	{
		$content = '<p>' .__d('taxonomy', 'Nothing here, yet!') .'</p>';

		return View::make('Default')
			->shares('title', $vocabulary->name)
			->with('content', $content);
	}

	protected function handleTerm(Vocabulary $vocabulary, $slug)
	{
		try {
			$term = Term::where('slug', $slug)->firstOrFail();
		}
		catch (ModelNotFoundException $e) {
			throw new NotFoundHttpException();
		}

		$content = '<p>' .__d('taxonomy', 'Nothing here, yet!') .'</p>';

		return View::make('Default')
			->shares('title', $vocabulary->name .' : ' .$term->name)
			->with('content', $content);
	}
}
