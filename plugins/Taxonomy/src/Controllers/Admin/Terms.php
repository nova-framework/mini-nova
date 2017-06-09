<?php

namespace Taxonomy\Controllers\Admin;

use Mini\Database\ORM\ModelNotFoundException;
use Mini\Http\Request;
use Mini\Support\Facades\Input;
use Mini\Support\Facades\Redirect;
use Mini\Support\Facades\Validator;
use Mini\Support\Str;

use Backend\Controllers\BaseController;
use Taxonomy\Models\Term;
use Taxonomy\Models\Vocabulary;
use Taxonomy\Support\Facades\Taxonomy;


class Terms extends BaseController
{
	public function __construct()
	{
		parent::__construct();

		// Setup the Middleware.
		$this->middleware('role:administrator');
	}

	protected function validator(array $data, $id = null)
	{
		if (! is_null($id)) {
			$ignore = ',' .intval($id);
		} else {
			$ignore = '';
		}

		// The Validation rules.
		$rules = array(
			'name'			=> 'required|min:3|max:100',
			'slug'			=> 'max:100',
			'description'	=> 'max:1000',
			'parent'		=> 'integer|min:0'
		);

		$messages = array(
			//
		);

		$attributes = array(
			'name'			=> __d('taxonomy', 'Name'),
			'slug'			=> __d('taxonomy', 'Slug'),
			'description'	=> __d('taxonomy', 'Description'),
			'parent'		=> __d('taxonomy', 'Parent'),
		);


		return Validator::make($data, $rules, $messages, $attributes);
	}

	public function index($vid)
	{
		// Get the Vocabulary Model instance.
		try {
			$vocabulary = Vocabulary::findOrFail($vid);
		}
		catch (ModelNotFoundException $e) {
			$status = __d('taxonomy', 'The Vocabulary with ID: {0} was not found.', $vid);

			return Redirect::to('admin/taxonomy')->with('warning', $status);
		}

		$terms = $vocabulary->terms()->where('parent_id', 0)->orderBy('weight', 'ASC')->get();

		return $this->shares('title', __d('taxonomy', 'View the Terms'))
			->with('vocabulary', $vocabulary)
			->with('terms', $terms);
	}

	public function create($vid)
	{
		// Get the Vocabulary Model instance.
		try {
			$vocabulary = Vocabulary::findOrFail($vid);
		}
		catch (ModelNotFoundException $e) {
			$status = __d('taxonomy', 'The Vocabulary with ID: {0} was not found.', $vid);

			return Redirect::to('admin/taxonomy')->with('warning', $status);
		}

		$options = Taxonomy::getVocabularyTermsAsOptionsArray($vocabulary);

		return $this->shares('title', __d('taxonomy', 'Create Term'))
			->with('vocabulary', $vocabulary)
			->with('options', $options);
	}

	public function store($vid)
	{
		// Get the Vocabulary Model instance.
		try {
			$vocabulary = Vocabulary::findOrFail($vid);
		}
		catch (ModelNotFoundException $e) {
			$status = __d('taxonomy', 'The Vocabulary with ID: {0} was not found.', $vid);

			return Redirect::to('admin/taxonomy')->with('warning', $status);
		}

		// Validate the Input data.
		$input = Input::only('name', 'slug', 'description', 'parent');

		//
		$validator = $this->validator($input);

		if($validator->passes()) {
			$slug = ! empty($input['slug']) ? $input['slug'] : $input['name'];

			$slug = Term::uniqueSlug($slug);

			// Create a Vocabulary Model instance.
			$term = new Term();

			//
			$term->name			= $input['name'];
			$term->slug			= $slug;
			$term->description	= $input['description'];
			$term->parent_id	= $input['parent'];

			$term->vocabulary_id = $vocabulary->id;

			// Save the User information.
			$term->save();

			// Prepare the flash message.
			$status = __d('taxonomy', 'The Term <b>{0}</b> was successfully created.', $input['name']);

			return Redirect::to('admin/taxonomy/' .$vocabulary->id .'/terms')->with('success', $status);
		}

		// Errors occurred on Validation.
		return Redirect::back()->withInput()->withErrors($validator->errors());
	}

	public function edit($vid, $id)
	{
		// Get the Vocabulary Model instance.
		try {
			$vocabulary = Vocabulary::findOrFail($vid);
		}
		catch (ModelNotFoundException $e) {
			$status = __d('taxonomy', 'The Vocabulary with ID: {0} was not found.', $vid);

			return Redirect::to('admin/taxonomy')->with('warning', $status);
		}

		// Get the Term Model instance.
		try {
			$term = Term::findOrFail($id);
		}
		catch (ModelNotFoundException $e) {
			$status = __d('taxonomy', 'The Term with ID: {0} was not found.', $id);

			return Redirect::to('admin/taxonomy/' .$vocabulary->id .'/terms')->with('warning', $status);
		}

		$options = Taxonomy::getVocabularyTermsAsOptionsArray($vocabulary);

		return $this->shares('title', __d('taxonomy', 'Edit Term'))
			->with('vocabulary', $vocabulary)
			->with('options', $options)
			->with('term', $term);
	}

	public function update($vid, $id)
	{
		// Get the Vocabulary Model instance.
		try {
			$vocabulary = Vocabulary::findOrFail($vid);
		}
		catch (ModelNotFoundException $e) {
			$status = __d('taxonomy', 'The Vocabulary with ID: {0} was not found.', $vid);

			return Redirect::to('admin/taxonomy')->with('warning', $status);
		}

		// Get the Term Model instance.
		try {
			$term = Term::findOrFail($id);
		}
		catch (ModelNotFoundException $e) {
			$status = __d('taxonomy', 'The Term with ID: {0} was not found.', $id);

			return Redirect::to('admin/taxonomy/' .$vocabulary->id .'/terms')->with('warning', $status);
		}

		// Validate the Input data.
		$input = Input::only('name', 'slug', 'description', 'parent');

		//
		$validator = $this->validator($input);

		if($validator->passes()) {
			$name = $term->name;

			$slug = ! empty($input['slug']) ? $input['slug'] : $input['name'];

			$slug = Term::uniqueSlug($slug, $term->id);

			//
			$term->name			= $input['name'];
			$term->slug			= $slug;
			$term->description	= $input['description'];
			$term->parent_id	= $input['parent'];

			// Save the User information.
			$term->save();

			// Prepare the flash message.
			$status = __d('taxonomy', 'The Term <b>{0}</b> was successfully updated.', $name);

			return Redirect::to('admin/taxonomy/' .$vocabulary->id .'/terms')->with('success', $status);
		}

		// Errors occurred on Validation.
		return Redirect::back()->withInput()->withErrors($validator->errors());
	}

	public function destroy($vid, $id)
	{
		// Get the Vocabulary Model instance.
		try {
			$vocabulary = Vocabulary::findOrFail($vid);
		}
		catch (ModelNotFoundException $e) {
			$status = __d('taxonomy', 'The Vocabulary with ID: {0} was not found.', $vid);

			return Redirect::to('admin/taxonomy')->with('warning', $status);
		}

		// Get the Term Model instance.
		try {
			$term = Term::findOrFail($id);
		}
		catch (ModelNotFoundException $e) {
			$status = __d('taxonomy', 'The Term with ID: {0} was not found.', $id);

			return Redirect::to('admin/taxonomy/' .$vocabulary->id .'/terms')->with('warning', $status);
		}

		// Recursivelly delete the requested Term record, its relationships and children.
		Taxonomy::deleteTerm($term->id);

		// Prepare the flash message.
		$status = __d('taxonomy', 'The Term <b>{0}</b> was successfully deleted.', $term->name);

		return Redirect::to('admin/taxonomy/' .$vocabulary->id .'/terms')->with('success', $status);
	}

	public function orderTerms(Request $request, $id)
	{
		// Get the Vocabulary Model instance.
		try {
			$vocabulary = Vocabulary::findOrFail($id);
		}
		catch (ModelNotFoundException $e) {
			return;
		}

		$input = Input::get('json');

		$items = json_decode($input);

		Taxonomy::updateTermsOrder($items);
	}
}
