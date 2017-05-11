<?php

namespace Mini\Http;

use Mini\Http\ResponseTrait;
use Mini\Session\Store as SessionStore;
use Mini\Support\MessageBag;
use Mini\Support\ViewErrorBag;
use Mini\Support\Contracts\MessageProviderInterface;
use Mini\Support\Str;

use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymfonyUploadedFile;


class RedirectResponse extends SymfonyRedirectResponse
{
	use ResponseTrait;

	/**
	 * The request instance.
	 *
	 * @var \Http\Request
	 */
	protected $request;

	/**
	 * The session store implementation.
	 *
	 * @var \Mini\Session\Store
	 */
	protected $session;


	/**
	 * Flash a piece of data to the session.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return \Mini\Http\RedirectResponse
	 */
	public function with($key, $value = null)
	{
		$key = is_array($key) ? $key : [$key => $value];

		foreach ($key as $k => $v) {
			$this->session->flash($k, $v);
		}

		return $this;
	}

	/**
	 * Add multiple cookies to the response.
	 *
	 * @param  array  $cookie
	 * @return $this
	 */
	public function withCookies(array $cookies)
	{
		foreach ($cookies as $cookie) {
			$this->headers->setCookie($cookie);
		}

		return $this;
	}

	/**
	 * Flash an array of input to the session.
	 *
	 * @param  array  $input
	 * @return $this
	 */
	public function withInput(array $input = null)
	{
		$input = $input ?: $this->request->input();

		$this->session->flashInput(array_filter($input, function ($value)
		{
			return ! $value instanceof SymfonyUploadedFile;
		}));

		return $this;
	}

	/**
	 * Flash an array of input to the session.
	 *
	 * @param  mixed  string
	 * @return $this
	 */
	public function onlyInput()
	{
		return $this->withInput($this->request->only(func_get_args()));
	}

	/**
	 * Flash an array of input to the session.
	 *
	 * @param  mixed  string
	 * @return \Mini\Http\RedirectResponse
	 */
	public function exceptInput()
	{
		return $this->withInput($this->request->except(func_get_args()));
	}

	/**
	 * Flash a container of errors to the session.
	 *
	 * @param  \Mini\Support\Contracts\MessageProviderInterface|array  $provider
	 * @param  string  $key
	 * @return $this
	 */
	public function withErrors($provider, $key = 'default')
	{
		$value = $this->parseErrors($provider);

		$this->session->flash(
			'errors', $this->session->get('errors', new ViewErrorBag)->put($key, $value)
		);

		return $this;
	}

	/**
	 * Parse the given errors into an appropriate value.
	 *
	 * @param  \Mini\Support\Contracts\MessageProviderInterface|array  $provider
	 * @return \Mini\Support\MessageBag
	 */
	protected function parseErrors($provider)
	{
		if ($provider instanceof MessageProviderInterface) {
			return $provider->getMessageBag();
		}

		return new MessageBag((array) $provider);
	}

	/**
	 * Get the request instance.
	 *
	 * @return  \Mini\Http\Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * Set the request instance.
	 *
	 * @param  \Mini\Http\Request  $request
	 * @return void
	 */
	public function setRequest(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * Get the session store implementation.
	 *
	 * @return \Session\Store
	 */
	public function getSession()
	{
		return $this->session;
	}

	/**
	 * Set the session store implementation.
	 *
	 * @param  \Session\Store  $session
	 * @return void
	 */
	public function setSession(SessionStore $session)
	{
		$this->session = $session;
	}

}
