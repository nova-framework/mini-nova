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


class Vocabularies extends BaseController
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
			'name'			=> 'required|min:3|max:100|valid_name',
			'slug'			=> 'max:100',
			'description'	=> 'max:1000',
		);

		$messages = array(
			'valid_name'	=> __d('taxonomy', 'The :attribute field is not a valid name.'),
		);

		$attributes = array(
			'name'			=> __d('taxonomy', 'Name'),
			'slug'			=> __d('taxonomy', 'Slug'),
			'description'	=> __d('taxonomy', 'Description'),
		);

		// Add the custom Validation Rule commands.
		Validator::extend('valid_name', function($attribute, $value, $parameters)
		{
			$pattern = '~^(?:[\p{L}\p{Mn}\p{Pd}\'\x{2019}]+(?:$|\s+)){1,}$~u';

			return (preg_match($pattern, $value) === 1);
		});

		return Validator::make($data, $rules, $messages, $attributes);
	}

	public function index()
	{
		$vocabularies = Vocabulary::with('terms')->paginate(10);

		return $this->getView()
			->shares('title', __d('taxonomy', 'Taxonomy'))
			->with('vocabularies', $vocabularies);
	}

	public function create()
	{
		return $this->getView()
			->shares('title', __d('taxonomy', 'Create Vocabulary'));
	}

	public function store()
	{
		// Validate the Input data.
		$input = Input::only('name', 'slug', 'description');

		//
		$validator = $this->validator($input);

		if($validator->passes()) {
			$slug = ! empty($input['slug']) ? $input['slug'] : $input['name'];

			$slug = Vocabulary::uniqueSlug($slug, $term->id);

			// Create a Vocabulary Model instance.
			$vocabulary = new Vocabulary();

			//
			$vocabulary->name			= $input['name'];
			$vocabulary->slug			= $slug;
			$vocabulary->description	= $input['description'];

			// Save the User information.
			$vocabulary->save();

			// Prepare the flash message.
			$status = __d('taxonomy', 'The Vocabulary <b>{0}</b> was successfully created.', $input['name']);

			return Redirect::to('admin/taxonomy')->with('success', $status);
		}

		// Errors occurred on Validation.
		return Redirect::back()->withInput()->withErrors($validator->errors());
	}

	public function edit($id)
	{
		// Get the Vocabulary Model instance.
		try {
			$vocabulary = Vocabulary::findOrFail($id);
		}
		catch (ModelNotFoundException $e) {
			$status = __d('taxonomy', 'The Vocabulary with ID: {0} was not found.', $id);

			return Redirect::to('admin/taxonomy')->with('warning', $status);
		}

		return $this->getView()
			->shares('title', __d('taxonomy', 'Edit Vocabulary'))
			->with('vocabulary', $vocabulary);
	}

	public function update($id)
	{
		// Get the Vocabulary Model instance.
		try {
			$vocabulary = Vocabulary::findOrFail($id);
		}
		catch (ModelNotFoundException $e) {
			$status = __d('taxonomy', 'The Vocabulary with ID: {0} was not found.', $id);

			return Redirect::to('admin/taxonomy')->with('warning', $status);
		}

		// Validate the Input data.
		$input = Input::only('name', 'slug', 'description');

		//
		$validator = $this->validator($input);

		if($validator->passes()) {
			$name = $vocabulary->name;

			$slug = ! empty($input['slug']) ? $input['slug'] : $input['name'];

			$slug = Vocabulary::uniqueSlug($slug, $vocabulary->id);

			//
			$vocabulary->name			= $input['name'];
			$vocabulary->slug			= $slug;
			$vocabulary->description	= $input['description'];

			// Save the User information.
			$vocabulary->save();

			// Prepare the flash message.
			$status = __d('taxonomy', 'The Vocabulary <b>{0}</b> was successfully updated.', $name);

			return Redirect::to('admin/taxonomy')->with('success', $status);
		}

		// Errors occurred on Validation.
		return Redirect::back()->withInput()->withErrors($validator->errors());
	}

	public function destroy($id)
	{
		// Get the Vocabulary Model instance.
		try {
			$vocabulary = Vocabulary::findOrFail($id);
		}
		catch (ModelNotFoundException $e) {
			$status = __d('taxonomy', 'The Vocabulary with ID: {0} was not found.', $id);

			return Redirect::to('admin/taxonomy')->with('warning', $status);
		}

		// Recursivelly delete the associated Terms.
		$terms = $vocabulary->terms()->get();

		foreach ($terms as $term) {
			Term::deleteTermAndChildren($term);
		}

		// Delete the requested Vocabulary record.
		$vocabulary->delete();

		// Prepare the flash message.
		$status = __d('taxonomy', 'The Vocabulary <b>{0}</b> was successfully deleted.', $vocabulary->name);

		return Redirect::to('admin/taxonomy')->with('success', $status);
	}
}
