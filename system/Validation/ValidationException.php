<?php

namespace Mini\Validation;

use Mini\Support\Contracts\MessageProviderInterface;

use RuntimeException;


class ValidationException extends RuntimeException
{
	/**
	 * The message provider implementation.
	 *
	 * @var \Mini\Support\Contracts\MessageProviderInterface
	 */
	protected $provider;

	/**
	 * Create a new validation exception instance.
	 *
	 * @param  \Mini\Support\MessageProvider  $provider
	 * @return void
	 */
	public function __construct(MessageProviderInterface $provider)
	{
		$this->provider = $provider;
	}

	/**
	 * Get the validation error message provider.
	 *
	 * @return \Mini\Support\MessageBag
	 */
	public function errors()
	{
		return $this->provider->getMessageBag();
	}

	/**
	 * Get the validation error message provider.
	 *
	 * @return \Mini\Support\MessageProviderInterface
	 */
	public function getMessageProvider()
	{
		return $this->provider;
	}
}
