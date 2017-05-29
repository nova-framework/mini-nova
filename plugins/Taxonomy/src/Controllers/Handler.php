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
		$terms = $vocabulary->terms()
			->with('children', 'relations')
			->where('parent_id', 0)
			->paginate(10);

		return View::make('Taxonomy::Handler/Vocabulary')
			->shares('title', $vocabulary->name)
			->with('vocabulary', $vocabulary)
			->with('terms', $terms);
	}

	protected function handleTerm(Vocabulary $vocabulary, $slug)
	{
		try {
			$term = Term::with('children', 'relations')->where('slug', $slug)->firstOrFail();
		}
		catch (ModelNotFoundException $e) {
			throw new NotFoundHttpException();
		}

		return View::make('Taxonomy::Handler/Term')
			->shares('title', $vocabulary->name .' : ' .$term->name)
			->with('vocabulary', $vocabulary)
			->with('term', $term);
	}
}
