<?php

namespace Mini\Container;

use ArrayAccess;
use Closure;
use ReflectionClass;
use ReflectionParameter;


class BindingResolutionException extends \Exception {}

class Container implements ArrayAccess
{
    /**
     * The container's bindings.
     *
     * @var array
     */
    protected $bindings = array();

    /**
     * The container's shared instances.
     *
     * @var array
     */
    protected $instances = array();


    /**
     * Determine if the given abstract type has been bound.
     *
     * @param  string  $abstract
     * @return bool
     */
    public function bound($abstract)
    {
        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * Register a binding with the container.
     *
     * @param  string   $abstract
     * @param  mixed    $concrete
     * @param  bool     $shared
     * @return void
     */
    public function bind($abstract, $concrete = null, $shared = false)
    {
        $this->dropStaleInstances($abstract);

        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        if (! $concrete instanceof Closure) {
            $concrete = $this->getClosure($abstract, $concrete);
        }

        $this->bindings[$abstract] = compact('concrete', 'shared');
    }

    /**
     * Get the Closure to be used when building a type.
     *
     * @param  string  $abstract
     * @param  string  $concrete
     * @return \Closure
     */
    protected function getClosure($abstract, $concrete)
    {
        return function($container) use ($abstract, $concrete)
        {
            $method = ($abstract == $concrete) ? 'build' : 'make';

            return call_user_func(array($container, $method), $concrete);
        };
    }

    /**
     * Register a shared binding in the container.
     *
     * @param  string   $abstract
     * @param  Closure  $concrete
     * @return void
     */
    public function singleton($abstract, $concrete = null)
    {
        $this->bind($abstract, $concrete, true);
    }

    /**
     * Wrap a Closure such that it is shared.
     *
     * @param  Closure  $closure
     * @return Closure
     */
    public function share(Closure $closure)
    {
        return function($container) use ($closure)
        {
            static $instance;

            if (is_null($instance)) {
                $instance = call_user_func($closure, $container);
            }

            return $instance;
        };
    }

    /**
     * Bind a shared Closure into the container.
     *
     * @param  string  $abstract
     * @param  \Closure  $closure
     * @return void
     */
    public function bindShared($abstract, Closure $closure)
    {
        return $this->bind($abstract, $this->share($closure), true);
    }

    /**
     * Register an existing instance as a singleton.
     *
     * @param  string  $abstract
     * @param  mixed   $instance
     * @return void
     */
    public function instance($abstract, $instance)
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Resolve a given type to an instance.
     *
     * @param  string  $abstract
     * @return mixed
     */
    public function make($abstract)
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (! isset($this->bindings[$abstract])) {
            $concrete = $abstract;
        } else {
            $concrete = $this->bindings[$abstract]['concrete'];
        }

        if ($concrete instanceof Closure) {
            $instance = call_user_func($concrete);
        } else if ($concrete === $abstract) {
            $instance = $this->build($concrete);
        } else {
            $instance = $this->make($concrete);
        }

        if ($this->isShared($abstract)) {
            $this->instances[$abstract] = $instance;
        }

        return $instance;
    }

    /**
     * Instantiate an instance of the given type.
     *
     * @param  string  $concrete
     * @param  array   $parameters
     * @return mixed
     */
    protected static function build($concrete)
    {
        $reflector = new ReflectionClass($concrete);

        if (! $reflector->isInstantiable()) {
            throw new BindingResolutionException("Resolution target [$concrete] is not instantiable.");
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            return new $concrete;
        }

        $dependencies = $constructor->getParameters();

        $arguments = $this->getDependencies($dependencies);

        return $reflector->newInstanceArgs($arguments);
    }

    /**
     * Resolve all of the dependencies from the ReflectionParameters.
     *
     * @param  array  $parameters
     * @return array
     */
    protected static function getDependencies($parameters)
    {
        $dependencies = array();

        foreach ($parameters as $parameter) {
            if (is_null($dependency = $parameter->getClass())) {
                $dependencies[] = $this->resolveNonClass($parameter);
            } else {
                $dependencies[] = $this->resolveClass($parameter);
            }
        }

        return $dependencies;
    }

    /**
     * Resolves optional parameters for our dependency injection
     *
     * @param ReflectionParameter
     * @return default value
     */
    protected static function resolveNonClass(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new BindingResolutionException("Unresolvable dependency resolving [$parameter].");
    }

    /**
     * Resolve a class based dependency from the container.
     *
     * @param  \ReflectionParameter  $parameter
     * @return mixed
     *
     * @throws BindingResolutionException
     */
    protected function resolveClass(ReflectionParameter $parameter)
    {
        try {
            $concrete = $parameter->getClass()->name;

            return $this->make($concrete);
        }
        catch (BindingResolutionException $e) {
            if (! $parameter->isOptional()) {
                throw $e;
            }
        }

        return $parameter->getDefaultValue();
    }

    /**
     * Determine if a given type is shared.
     *
     * @param  string  $abstract
     * @return bool
     */
    public function isShared($abstract)
    {
        if (isset($this->bindings[$abstract]['shared'])) {
            $shared = $this->bindings[$abstract]['shared'];
        } else {
            $shared = false;
        }

        return isset($this->instances[$abstract]) || ($shared === true);
    }

    /**
     * Drop all of the stale instances and aliases.
     *
     * @param  string  $abstract
     * @return void
     */
    protected function dropStaleInstances($abstract)
    {
        unset($this->instances[$abstract]);

        unset($this->aliases[$abstract]);
    }

    /**
     * Sets a parameter or an object.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function offsetSet($key, $value)
    {
        if (! $value instanceof Closure) {
            $value = function() use ($value)
            {
                return $value;
            };
        }

        $this->bind($key, $value);
    }

    /**
     * Gets a parameter or an object.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->make($key);
    }

    /**
     * Checks if a parameter or an object is set.
     *
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->bindings[$key]);
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $key
     */
    public function offsetUnset($key)
    {
        unset($this->bindings[$key]);

        unset($this->instances[$key]);
    }

}
