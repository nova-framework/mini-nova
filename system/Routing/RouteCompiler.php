<?php

namespace Mini\Routing;

use Mini\Support\Arr;

use DomainException;
use LogicException;


class RouteCompiler
{
	const REGEX_DELIMITER = '#';

	/**
	 * The default regex pattern used for the named parameters.
	 *
	 */
	const DEFAULT_PATTERN = '[^/]+';


	/**
	 * Compile an URI pattern to a valid regexp.
	 *
	 * @param  string   $uri
	 * @param  array	$requirements
	 * @return string
	 */
	public static function compile($uri, $requirements = array())
	{
		$uri = '/' .ltrim($uri, '/');

		list($pattern, $optionals) = static::compilePattern($uri, $requirements);

		return static::computeRegexp($pattern, $optionals);
	}

	/**
	 * Compile an URI pattern.
	 *
	 * @param  string   $uri
	 * @param  array	$requirements
	 * @return string
	 *
	 * @throw \LogicException
	 */
	public static function compilePattern($uri, $requirements)
	{
		$optionals = array();

		//
		$parameters = array();

		$regexp = preg_replace_callback('#/{(\w+)(?:(\?))?}#i', function ($matches) use ($uri, $requirements, &$optionals, &$parameters)
		{
			$prefix = '';

			@list(, $parameter, $optional) = $matches;

			if (in_array($parameter, $parameters)) {
				$message = sprintf('Route pattern [%s] cannot reference parameter name [%s] more than once.', $uri, $parameter);

				throw new LogicException($message);
			}

			// Handle the optional parameters.
			if ($optional === '?') {
				$prefix = '(?:';

				array_push($optionals, $parameter);
			} else if (count($optionals) > 0) {
				$message = sprintf('Route pattern [%s] cannot reference standard parameter [%s] after optionals.', $uri, $parameter);

				throw new LogicException($message);
			}

			$pattern = Arr::get($requirements, $parameter, self::DEFAULT_PATTERN);

			//
			array_push($parameters, $parameter);

			return sprintf('%s/(?P<%s>%s)', $prefix, $parameter, $pattern);

		}, $uri);

		return array($regexp, $optionals);
	}

	/**
	 * Computes the regexp used to match a specific route pattern.
	 *
	 * @param  string   $pattern
	 * @param  array	$optionals
	 * @return string
	 */
	public static function computeRegexp($pattern, $optionals = array())
	{
		if (! empty($optionals)) {
			// When the optionals are present, we just need to adjust the pattern.
			$pattern .= str_repeat(')?', count($optionals));
		}

		return self::REGEX_DELIMITER .'^' .$pattern .'$' .self::REGEX_DELIMITER .'s';
	}
}
