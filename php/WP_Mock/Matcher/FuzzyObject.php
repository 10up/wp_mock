<?php

namespace WP_Mock\Matcher;

use Mockery\Exception as MockeryException;
use Mockery\Matcher\MatcherAbstract;

/**
 * Fuzzy object class.
 */
class FuzzyObject extends MatcherAbstract
{
    /**
     * Constructor.
     *
     * @param object|array|mixed $expected
     * @throws MockeryException if a non-object non-array expectation is provided
     */
    public function __construct($expected = null)
    {
        if (! is_object($expected)) {
            if (is_array($expected)) {
                $expected = (object) $expected;
            } else {
                throw new MockeryException('FuzzyObject matcher can only match objects!');
            }
        }

        parent::__construct($expected);
    }

    /**
     * Checks if the actual value matches the expected.
     *
     * Actual passed by reference to preserve reference trail (where applicable) back to the original method parameter.
     *
     * @param mixed $actual
     * @return bool
     */
    public function match(&$actual): bool
    {
        if (! is_object($actual)) {
            return false;
        }

        if (! $this->haveCommonAncestor($actual, $this->_expected)) {
            return false;
        }

        $expectedProperties = is_object($this->_expected) ? get_object_vars($this->_expected) : [];

        foreach ($expectedProperties as $prop => $value) {
            if (! isset($actual->$prop) || $value !== $actual->$prop) {
                return false;
            }
        }

        $actual_keys = array_keys(get_object_vars($actual));
        $extra_actual = array_diff($actual_keys, array_keys($expectedProperties));

        if (! empty($extra_actual)) {
            return false;
        }

        return true;
    }

    /**
     * Returns a string representation of this Matcher.
     *
     * @return string
     */
    public function __toString(): string
    {
        $values = array_values(is_object($this->_expected) ? get_object_vars($this->_expected) : []);
        $values = array_map(function ($value) {
            if (! is_scalar($value)) {
                if (is_array($value)) {
                    $value = 'Array';
                } elseif (is_object($value)) {
                    $value = get_class($value);
                } elseif (is_resource($value)) {
                    $value = get_resource_type($value);
                } else {
                    $value = 'unknown';
                }
            }
            return $value;
        }, $values);

        return '<FuzzyObject['.implode(', ', $values).']>';
    }

    /**
     * Determines if two objects have a common ancestor.
     *
     * @param object|mixed $object1
     * @param object|mixed $object2
     * @return bool
     */
    protected function haveCommonAncestor($object1, $object2): bool
    {
        if (! is_object($object1) || ! is_object($object2)) {
            return false;
        }

        $class1 = get_class($object1);
        $class2 = get_class($object2);

        if ($class1 === $class2) {
            return true;
        }

        $class1parents = class_parents($class1) ?: [];
        $class2parents = class_parents($class2) ?: [];

        return in_array($class1, $class2parents) || in_array($class2, $class1parents);
    }
}
