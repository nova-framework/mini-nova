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

	public function handle($group, $slug = null)
	{
		try {
			$vocabulary = Vocabulary::where('slug', $group)->firstOrFail();
		}
		catch (ModelNotFoundException $e) {
			throw new NotFoundHttpException();
		}

		if (is_null($slug)) {
			return $this->handleVocabulary($vocabulary);
		}

		try {
			$term = Term::with('children', 'relations')->where('slug', $slug)->firstOrFail();
		}
		catch (ModelNotFoundException $e) {
			throw new NotFoundHttpException();
		}

		return $this->handleTerm($vocabulary, $term);
	}

	protected function handleVocabulary(Vocabulary $vocabulary)
	{
		$terms = $vocabulary->terms()
			->with('children', 'relations')
			->where('parent_id', 0)
			->paginate(10);

		//
		$this->set('title', $vocabulary->name);

		return $this->createView(compact('vocabulary', 'terms'), 'Vocabulary');
	}

	protected function handleTerm(Vocabulary $vocabulary, Term $term)
	{
		$this->set('title', $term->name);

		return $this->createView(compact( 'vocabulary', 'term'), 'Term');
	}
}
