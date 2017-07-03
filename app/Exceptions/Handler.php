<?php

namespace App\Exceptions;

use Mini\Auth\AuthenticationException;
use Mini\Http\Response;
use Mini\Foundation\Exceptions\Handler as ExceptionHandler;
use Mini\Session\TokenMismatchException;
use Mini\Support\Facades\View;
use Mini\Support\Facades\Redirect;

use Exception;


class Handler extends ExceptionHandler
{
	/**
	 * A list of the exception types that should not be reported.
	 *
	 * @var array
	 */
	protected $dontReport = array(
		'Mini\Auth\AuthenticationException',
		'Symfony\Component\HttpKernel\Exception\HttpException',
		'Mini\Database\ORM\ModelNotFoundException',
		'Mini\Session\TokenMismatchException',
		'Mini\Validation\ValidationException',
	);


	/**
	 * Report or log an exception.
	 *
	 * @param  \Exception  $e
	 * @return void
	 */
	public function report(Exception $e)
	{
		return parent::report($e);
	}

	/**
	 * Render an exception into an HTTP response.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @param  \Exception  $e
	 * @return \Mini\Http\Response
	 */
	public function render($request, Exception $e)
	{
		if ($e instanceof TokenMismatchException) {
			return Redirect::back()
				->withInput()
				->with('warning', __('Your session has expired. Please try again!'));
		}

		// If we got a HttpException, we will render a themed error page.
		else if ($this->isHttpException($e)) {
			$status = $e->getStatusCode();

			if (View::exists("Errors/{$status}")) {
				$data = array('exception' => $e);

				$content = View::make('Layouts/Default')
					->shares('title', "Error {$status}")
					->nest('content', "Errors/{$status}", $data)
					->render();

				return new Response($content, $status, $e->getHeaders());
			}
		}

		return parent::render($request, $e);
	}

	/**
	 * Convert an authentication exception into an unauthenticated response.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @param  \Mini\Auth\AuthenticationException  $exception
	 * @return \Mini\Http\Response
	 */
	protected function unauthenticated($request, AuthenticationException $exception)
	{
		if ($request->expectsJson()) {
			return Response::json(array('error' => 'Unauthenticated.'), 401);
		}

		return Redirect::guest('login');
	}
}
