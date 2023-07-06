<?php

namespace WP_Mock\Traits;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Trait for accessing inaccessible class members through reflection.
 */
trait AccessInaccessibleClassMembersTrait
{
    /**
     * Gets the given inaccessible method for the given class.
     *
     * Allows for calling protected and private methods on a class.
     *
     * @param class-string|object $class the class name or instance
     * @param string $methodName the method name
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    public function getInaccessibleMethod($class, string $methodName): ReflectionMethod
    {
        $class = new ReflectionClass($class);

        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * Invokes the given inaccessible method on the given class.
     *
     * @param object $class the class name or instance to call against
     * @param string $methodName the method name to call
     * @param mixed ...$args arguments to pass to the invoked method
     * @return mixed
     * @throws ReflectionException
     */
    public function invokeInaccessibleMethod(object $class, string $methodName, ...$args)
    {
        return $this->getInaccessibleMethod($class, $methodName)->invoke($class, ...$args);
    }

    /**
     * Gets the given inaccessible property for the given class.
     *
     * Allows for calling protected and private properties on a class.
     *
     * @param class-string|object $class the class name or instance
     * @param string $propertyName the property name
     * @return ReflectionProperty
     * @throws ReflectionException
     */
    public function getInaccessibleProperty($class, string $propertyName): ReflectionProperty
    {
        $class = new ReflectionClass($class);

        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);

        return $property;
    }

    /**
     * Gets the given inaccessible property value for the given class.
     *
     * Allows for calling protected and private properties on a class.
     *
     * @param object $class the class name or instance
     * @param string $property the property name
     * @return mixed the property value
     * @throws ReflectionException
     */
    public function getInaccessiblePropertyValue(object $class, string $property)
    {
        return $this->getInaccessibleProperty($class, $property)->getValue($class);
    }

    /**
     * Allows for setting private or protected properties in a class.
     *
     * @param object|null $instance class instance or null for static classes
     * @param class-string $class
     * @param string $property
     * @param mixed $value
     * @return ReflectionProperty
     * @throws ReflectionException
     */
    public function setInaccessibleProperty($instance, string $class, string $property, $value): ReflectionProperty
    {
        $class = new ReflectionClass($class);

        $property = $class->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($instance, $value);

        return $property;
    }

    /**
     * Sets a private or protected property on a class.
     *
     * @param object $instance class instance
     * @param string $property the property to set
     * @param mixed $value the value to set on the property
     * @return ReflectionProperty
     * @throws ReflectionException
     */
    public function setInaccessiblePropertyValue(object $instance, string $property, $value) : ReflectionProperty
    {
        return $this->setInaccessibleProperty($instance, get_class($instance), $property, $value);
    }
}
