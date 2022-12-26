<?php

namespace WP_Mock\Matcher;

use Closure;
use Mockery\Exception as MockeryException;
use ReflectionClass;
use ReflectionException;

/**
 * Matcher for any class type.
 */
class AnyInstance extends FuzzyObject
{
    /**
     * Constructor.
     *
     * @param string|object $expected A classname or instance of a class whose type should match
     * @throws MockeryException|ReflectionException
     * @phpstan-ignore-next-line
     */
    public function __construct($expected = null)
    {
        if (is_string($expected) && class_exists($expected)) {
            $reflectedExpected = new \ReflectionClass($expected);
            $expectedInstance = $reflectedExpected->newInstanceWithoutConstructor();
        } elseif (is_object($expected)) {
            $expectedInstance = $expected;
        } else {
            throw new MockeryException('AnyInstance matcher can only match objects!');
        }

        parent::__construct($expectedInstance);
    }

    /**
     * Checks if the actual value matches the expected.
     *
     * Actual passed by reference to preserve reference trail (where applicable) back to the original method parameter.
     *
     * @param mixed $actual
     * @return bool
     * @throws ReflectionException
     */
    public function match(&$actual): bool
    {
        if (! is_object($actual)) {
            return false;
        }

        if ($actual instanceof Closure) {
            return false;
        }

        /** @phpstan-ignore-next-line */
        if (get_class($actual) === get_class($this->_expected)) {
            return true;
        }

        /** @phpstan-ignore-next-line parent::haveCommonAncestor() expects two objects */
        $reflectedExpected = new ReflectionClass($this->_expected);
        $expectedInstance = $reflectedExpected->newInstanceWithoutConstructor();

        if (! $this->haveCommonAncestor($actual, $expectedInstance)) {
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
        /** @phpstan-ignore-next-line */
        $classname = get_class($this->_expected);

        return "<AnyInstance[{$classname}]>";
    }
}
