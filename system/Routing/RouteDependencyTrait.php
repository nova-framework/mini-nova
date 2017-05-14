<?php

namespace Mini\Routing;

use Mini\Support\Arr;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionFunctionAbstract;


trait RouteDependencyTrait
{
	/**
	 * Call a class method with the resolved dependencies.
	 *
	 * @param  object  $instance
	 * @param  string  $method
	 * @return mixed
	 */
	protected function callWithDependencies($instance, $method)
	{
		$parameters = $this->resolveClassMethodDependencies(array(), $instance, $method);

		return call_user_func_array(array($instance, $method), $parameters);
	}

	/**
	 * Resolve the object method's type-hinted dependencies.
	 *
	 * @param  array  $parameters
	 * @param  object  $instance
	 * @param  string  $method
	 * @return array
	 */
	protected function resolveClassMethodDependencies(array $parameters, $instance, $method)
	{
		if (! method_exists($instance, $method)) {
			return $parameters;
		}

		return $this->resolveMethodDependencies(
			$parameters, new ReflectionMethod($instance, $method)
		);
	}

	/**
	 * Resolve the given method's type-hinted dependencies.
	 *
	 * @param  array  $parameters
	 * @param  \ReflectionFunctionAbstract  $reflector
	 * @return array
	 */
	public function resolveMethodDependencies(array $parameters, ReflectionFunctionAbstract $reflector)
	{
		$originalParameters = $parameters;

		foreach ($reflector->getParameters() as $key => $parameter) {
			$instance = $this->transformDependency($parameter, $parameters);

			if (! is_null($instance)) {
				$this->spliceIntoParameters($parameters, $key, $instance);
			}
		}

		return $parameters;
	}

	/**
	 * Attempt to transform the given parameter into a class instance.
	 *
	 * @param  \ReflectionParameter  $parameter
	 * @param  array  $parameters
	 * @return mixed
	 */
	protected function transformDependency(ReflectionParameter $parameter, $parameters)
	{
		$class = $parameter->getClass();

		if ($class && ! $this->alreadyInParameters($class->name, $parameters)) {
			return $this->container->make($class->name);
		}
	}

	/**
	 * Determine if an object of the given class is in a list of parameters.
	 *
	 * @param  string  $class
	 * @param  array  $parameters
	 * @return bool
	 */
	protected function alreadyInParameters($class, array $parameters)
	{
		$result = Arr::first($parameters, function ($key, $value) use ($class)
		{
			return ($value instanceof $class);
		});

		return ! is_null($result);
	}

	/**
	 * Splice the given value into the parameter list.
	 *
	 * @param  array  $parameters
	 * @param  string  $key
	 * @param  mixed  $instance
	 * @return void
	 */
	protected function spliceIntoParameters(array &$parameters, $key, $instance)
	{
		array_splice($parameters, $key, 0, array($instance));
	}
}