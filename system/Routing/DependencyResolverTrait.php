<?php

namespace Mini\Routing;

use ReflectionMethod;
use ReflectionParameter;
use ReflectionFunctionAbstract;


trait DependencyResolverTrait
{

	/**
	 * Call a class method with the resolved dependencies.
	 *
	 * @param  object  $instance
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	protected function callWithDependencies($instance, $method, array $parameters = array())
	{
		return call_user_func_array(
			array($instance, $method), $this->resolveClassMethodDependencies($parameters, $instance, $method)
		);
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
	protected function resolveMethodDependencies(array $parameters, ReflectionFunctionAbstract $reflector)
	{
		$dependencies = array();

		foreach ($reflector->getParameters() as $parameter) {
			$this->addDependencyForCallParameter($parameter, $parameters, $dependencies);

		}

		return array_merge($dependencies, $parameters);
	}

	/**
	 * Get the dependency for the given call parameter.
	 *
	 * @param  \ReflectionParameter  $parameter
	 * @param  array  $parameters
	 * @param  array  $dependencies
	 * @return mixed
	 */
	protected function addDependencyForCallParameter(ReflectionParameter $parameter, array &$parameters, &$dependencies)
	{
		if (array_key_exists($parameter->name, $parameters)) {
			$key = $parameter->name;

			$dependencies[] = $parameters[$key];

			unset($parameters[$key]);
		} else if (! is_null($class = $parameter->getClass())) {
			$dependencies[] = $this->container->make($class->name);
		} else if ($parameter->isDefaultValueAvailable()) {
			$dependencies[] = $parameter->getDefaultValue();
		}
	}

}
