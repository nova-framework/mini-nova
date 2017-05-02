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
     * @param  string   $uri
     * @param  array    $patterns
     * @return string
     *
     * @throw \LogicException
     */
    protected static function compilePattern($uri, $patterns)
    {
        $optionals = array();

        //
        $parameters = array();

        $regexp = preg_replace_callback('#/{(\w+)(?:(\?))?}#i', function ($matches) use ($uri, $patterns, &$optionals, &$parameters)
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
            } else if ($optionals > 0) {
                $message = sprintf('Route pattern [%s] cannot reference standard parameter [%s] after optionals.', $uri, $parameter);

                throw new LogicException($message);
            }

            $pattern = Arr::get($patterns, $parameter, self::DEFAULT_PATTERN);

            //
            array_push($parameters, $parameter);

            return sprintf('%s/(?P<%s>%s)', $prefix, $parameter, $pattern);

        }, $uri);

        return static::computeRegexp($regexp, $optionals);
    }

    /**
     * Computes the regexp used to match a specific route pattern.
     *
     * @param  string   $pattern
     * @param  array    $optionals
     * @return string
     */
    public static function computeRegexp($pattern, $optionals = array())
    {
        if (! empty($optionals)) {
            // When the optionals are present, we need to adjust the pattern.
            $pattern .= str_repeat(')?', count($optionals));
        }

        return self::REGEX_DELIMITER .'^' .$pattern .'$' .self::REGEX_DELIMITER .'s';
    }
}
