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
    const REGEX_PATTERN = '[^/]+';

    /**
     * The maximum supported length of a PCRE subpattern name
     * http://pcre.org/current/doc/html/pcre2pattern.html#SEC16.
     */
    const VARIABLE_MAXIMUM_LENGTH = 32;


    /**
     * Compile an URI pattern to a valid regexp.
     *
     * @param  string   $uri
     * @param  array    $patterns
     * @return string
     */
    public static function compile($uri, $patterns = array())
    {
        $pattern = '/' .ltrim($uri, '/');

        return static::compilePattern($pattern, $patterns);
    }

    /**
     * Compile an URI pattern to a valid regexp.
     *
     * @param  string   $pattern
     * @param  array    $patterns
     * @return string
     *
     * @throw \LogicException
     */
    protected static function compilePattern($pattern, $patterns)
    {
        $optionals = 0;

        // Replace the named parameters with their associated patterns.
        $variables = array();

        $regexp = preg_replace_callback('#/{(\w+)(?:(\?))?}#i', function ($matches) use ($pattern, $patterns, &$optionals, &$variables)
        {
            @list(, $varName, $optional) = $matches;

            // A PCRE subpattern name must start with a non-digit. Also a PHP variable cannot start
            // with a digit so the variable would not be usable as a Controller action argument.

            //if (preg_match('/^\d/', $varName) === 1) {
            if (ctype_digit(substr($varName, 0, 1))) {
                $message = sprintf('Variable name [%s] cannot start with a digit in route pattern [%s].', $varName, $pattern);

                throw new DomainException($message);
            }

            if (in_array($varName, $variables)) {
                $message = sprintf('Route pattern [%s] cannot reference variable name [%s] more than once.', $pattern, $varName);

                throw new LogicException($message);
            }

            if (strlen($varName) > self::VARIABLE_MAXIMUM_LENGTH) {
                $message = sprintf('Variable name [%s] cannot be longer than %d characters in route pattern [%s].',
                    $varName,
                    self::VARIABLE_MAXIMUM_LENGTH,
                    $pattern
                );

                throw new DomainException($message);
            }

            // Add the variable name to already used variables list.
            array_push($variables, $varName);

            // Process for the optional parameters.
            $prefix = '';

            if ($optional === '?') {
                $prefix = '(?:';

                $optionals++;
            } else if ($optionals > 0) {
                $message = sprintf('Route pattern [%s] cannot reference standard parameter [%s] after optional parameters.', $pattern, $varName);

                throw new LogicException($message);
            }

            // Get the regex pattern associated with the variable name.
            $regexp = Arr::get($patterns, $varName, self::REGEX_PATTERN);

            return sprintf('%s/(?P<%s>%s)', $prefix, $varName, $regexp);

        }, $pattern);

        // When the optional parameters are present, adjust the regex pattern for proper ending.
        if ($optionals > 0) {
            $regexp .= str_repeat(')?', $optionals);
        }

        return static::computeRegexp($regexp);
    }

    /**
     * Computes the regexp used to match a specific route pattern.
     *
     * @param  string   $pattern
     * @return string
     */
    public static function computeRegexp($pattern)
    {
        return self::REGEX_DELIMITER .'^' .$pattern .'$' .self::REGEX_DELIMITER .'s';
    }
}
