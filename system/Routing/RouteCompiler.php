<?php

namespace Mini\Routing;

use DomainException;
use LogicException;


class RouteCompiler
{
	/**
	 * The default regex pattern used for the named parameters.
	 *
	 */
	const DEFAULT_PATTERN = '[^/]+';


	/**
	 * Compile an URI pattern to a valid regexp.
	 *
	 * @param  string   $uri
	 * @param  array	$patterns
	 * @return string
	 *
	 * @throw \LogicException
	 */
	public static function compile($uri, $patterns)
	{
		$optionals = 0;

		//
		$variables = array();

		$regexp = preg_replace_callback('#/\{(.*?)(\?)?\}#', function ($matches) use ($uri, $patterns, &$optionals, &$variables)
		{
			@list(, $name, $optional) = $matches;

			// Check if the parameter name is unique.
			if (in_array($name, $variables)) {
				$message = sprintf('Route pattern [%s] cannot reference variable name [%s] more than once.', $uri, $name);

				throw new LogicException($message);
			}

			array_push($variables, $name);

			// Process for the optional parameters.
			$prefix = '';

			if (! is_null($optional)) {
				$prefix = '(?:';

				$optionals++;
			} else if ($optionals > 0) {
				$message = sprintf('Route pattern [%s] cannot reference variable [%s] after one or more optionals.', $uri, $name);

				throw new LogicException($message);
			}

			// Compute the parameter's pattern.
			$pattern = isset($patterns[$name]) ? $patterns[$name] : self::DEFAULT_PATTERN;

			return sprintf('%s/(?P<%s>%s)', $prefix, $name, $pattern);

		}, $uri);

		// Adjust the pattern when we have optional parameters.
		if ($optionals > 0) {
			$regexp .= str_repeat(')?', $optionals);
		}

		return array($regexp, $variables);
	}
}
