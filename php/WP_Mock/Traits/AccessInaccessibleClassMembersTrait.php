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
}
