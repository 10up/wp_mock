<?php

namespace WP_Mock;

/**
 * Internal handler for deprecated method calls.
 *
 * Flags usage of deprecated WP_Mock methods by emitting an {@see E_USER_DEPRECATED} notice,
 * which PHPUnit captures and attributes to the running test natively: it is reported per test
 * and fails the suite when `failOnDeprecation="true"` is set in the PHPUnit configuration.
 *
 * To flag a method as deprecated, call the following from within the deprecated method's logic:
 *
 *     \WP_Mock::getDeprecatedMethodListener()->logDeprecatedCall(__METHOD__, func_get_args());
 */
class DeprecatedMethodListener
{
    /** @var array<array{string, array<mixed>}> array of logged deprecated method calls with their arguments, if any */
    protected $deprecatedCalls = [];

    /** @var string */
    protected $testName = 'test';

    /**
     * Sets the test name in context.
     *
     * @param string $testName
     * @return $this
     */
    public function setTestName(string $testName): DeprecatedMethodListener
    {
        $this->testName = $testName;

        return $this;
    }

    /**
     * Logs a deprecated method call and emits a deprecation notice.
     *
     * The call is recorded (for inspection via {@see DeprecatedMethodListener::reset()} consumers)
     * and an {@see E_USER_DEPRECATED} notice is triggered immediately so PHPUnit reports it against
     * the running test.
     *
     * @param string $method
     * @param array<mixed> $args
     * @return $this
     */
    public function logDeprecatedCall(string $method, array $args = []): DeprecatedMethodListener
    {
        $this->deprecatedCalls[] = [$method, $args];

        trigger_error($this->buildMessage($method, $args), E_USER_DEPRECATED);

        return $this;
    }

    /**
     * Resets tracking of deprecated method calls.
     *
     * @return $this
     */
    public function reset(): DeprecatedMethodListener
    {
        $this->deprecatedCalls = [];

        return $this;
    }

    /**
     * Builds the deprecation message for a single deprecated method call.
     *
     * @param string $method
     * @param array<mixed> $args
     * @return string
     */
    protected function buildMessage(string $method, array $args): string
    {
        $message = sprintf('Deprecated WP_Mock call inside %s: %s', $this->testName, $method);

        if (! empty($args)) {
            $message .= ' '.json_encode(array_map([$this, 'toScalar'], $args));
        }

        return $message;
    }

    /**
     * Transforms a value for use in a JSON string.
     *
     * @param mixed $value
     * @return string|bool|null|float|int
     */
    protected function toScalar($value)
    {
        if ($value === null) {
            return null;
        } elseif (is_scalar($value)) {
            return $value;
        } elseif (is_object($value)) {
            return '<'.get_class($value).':'.spl_object_hash($value).'>';
        } elseif (is_array($value)) {
            if (is_callable($value)) {
                /** @phpstan-ignore-next-line */
                return '['.implode(',', array_map(array($this, 'toScalar'), $value)).']';
            } else {
                return 'Array(['.count($value).'] ...)';
            }
        } elseif (is_resource($value)) {
            return 'Resource';
        }

        return 'Unknown Value';
    }
}
