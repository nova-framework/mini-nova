<?php

namespace Mini\Foundation\Validation;

use Mini\Http\Exception\HttpResponseException;
use Mini\Http\Request;
use Mini\Http\JsonResponse;
use Mini\Routing\UrlGenerator;
use Mini\Validation\Factory;
use Mini\Validation\Validator;
use Mini\Support\Facades\Redirect;


trait ValidatesRequestsTrait
{
	/**
	 * The default error bag.
	 *
	 * @var string
	 */
	protected $validatesRequestErrorBag;


	/**
	 * Validate the given request with the given rules.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @param  array  $rules
	 * @param  array  $messages
	 * @param  array  $customAttributes
	 * @return void
	 *
	 * @throws \Mini\Http\Exception\HttpResponseException
	 */
	public function validate(Request $request, array $rules, array $messages = array(), array $customAttributes = array())
	{
		$validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);

		if ($validator->fails()) {
			$this->throwValidationException($request, $validator);
		}
	}

	/**
	 * Validate the given request with the given rules.
	 *
	 * @param  string  $errorBag
	 * @param  \Mini\Http\Request  $request
	 * @param  array  $rules
	 * @param  array  $messages
	 * @param  array  $customAttributes
	 * @return void
	 *
	 * @throws \Mini\Http\Exception\HttpResponseException
	 */
	public function validateWithBag($errorBag, Request $request, array $rules, array $messages = array(), array $customAttributes = array())
	{
		$this->withErrorBag($errorBag, function () use ($request, $rules, $messages, $customAttributes)
		{
			$this->validate($request, $rules, $messages, $customAttributes);
		});
	}

	/**
	 * Throw the failed validation exception.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @param  \Mini\Validation\Validator  $validator
	 * @return void
	 *
	 * @throws \Mini\Http\Exception\HttpResponseException
	 */
	protected function throwValidationException(Request $request, $validator)
	{
		throw new HttpResponseException($this->buildFailedValidationResponse(
			$request, $this->formatValidationErrors($validator)
		));
	}

	/**
	 * Create the response for when a request fails validation.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @param  array  $errors
	 * @return \Mini\Http\Response
	 */
	protected function buildFailedValidationResponse(Request $request, array $errors)
	{
		if ($request->ajax() || $request->wantsJson()) {
			return new JsonResponse($errors, 422);
		}

		$url = $this->getRedirectUrl();

		return Redirect::to($url)
			->withInput($request->input())
			->withErrors($errors, $this->errorBag());
	}

	/**
	 * Format the validation errors to be returned.
	 *
	 * @param  \Mini\Validation\Validator  $validator
	 * @return array
	 */
	protected function formatValidationErrors(Validator $validator)
	{
		return $validator->errors()->getMessages();
	}

	/**
	 * Get the URL we should redirect to.
	 *
	 * @return string
	 */
	protected function getRedirectUrl()
	{
		return app(UrlGenerator::class)->previous();
	}

	/**
	 * Get a validation factory instance.
	 *
	 * @return \Mini\Validation\Factory
	 */
	protected function getValidationFactory()
	{
		return app(Factory::class);
	}

	/**
	 * Execute a Closure within with a given error bag set as the default bag.
	 *
	 * @param  string  $errorBag
	 * @param  callable  $callback
	 * @return void
	 */
	protected function withErrorBag($errorBag, callable $callback)
	{
		$this->validatesRequestErrorBag = $errorBag;

		call_user_func($callback);

		$this->validatesRequestErrorBag = null;
	}

	/**
	 * Get the key to be used for the view error bag.
	 *
	 * @return string
	 */
	protected function errorBag()
	{
		return $this->validatesRequestErrorBag ?: 'default';
	}
}
