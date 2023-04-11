<?php

namespace WP_Mock;

use Mockery\MockInterface;
use PHPUnit\Framework\RiskyTestError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;

/**
 * Internal handler for deprecated method calls.
 *
 * This handler is used by WP_Mock to alert developers if they are using any WP_Mock deprecated methods.
 * Test cases using WP_Mock deprecated methods will report as risky.
 * In this way we can ensure that developers are aware of the deprecation and can update their code before any deprecated methods are permanently removed.
 *
 * To flag a method as deprecated use {@see \WP_Mock::getDeprecatedMethodListener()->logDeprecatedCall()} within a deprecated method's logic.
 */
class DeprecatedMethodListener
{
    /** @var array<array{string, array<mixed>}> array of logged deprecated method calls with their arguments, if any */
    protected $deprecatedCalls = [];

    /** @var string */
    protected $testName = 'test';

    /** @var TestCase|MockInterface */
    protected $testCase;

    /** @var TestResult|MockInterface */
    protected $testResult;

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
     * Sets the test case in context.
     *
     * @param TestCase|MockInterface $testCase
     * @return $this
     */
    public function setTestCase($testCase): DeprecatedMethodListener
    {
        $this->testCase = $testCase;

        return $this;
    }

    /**
     * Sets the test result in context.
     *
     * @param TestResult|MockInterface $testResult
     * @return $this
     */
    public function setTestResult($testResult): DeprecatedMethodListener
    {
        $this->testResult = $testResult;

        return $this;
    }

    /**
     * Logs a deprecated method call.
     *
     * @param string $method
     * @param array<mixed> $args
     * @return $this
     */
    public function logDeprecatedCall(string $method, array $args = []): DeprecatedMethodListener
    {
        $this->deprecatedCalls[] = [$method, $args];

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
     * Checks for deprecated method calls.
     *
     * Adds failures to the test result if any are found.
     *
     * @return void
     */
    public function checkCalls(): void
    {
        if (empty($this->deprecatedCalls)) {
            return;
        }

        $error = new RiskyTestError($this->buildErrorMessage());

        /** @phpstan-ignore-next-line */
        $this->testResult->addFailure($this->testCase, $error, 0);
    }

    /**
     * Gets a deprecated method call usage message.
     *
     * @return string
     */
    protected function buildErrorMessage(): string
    {
        $maxLength = array_reduce($this->getDeprecatedMethods(), function ($carry, $item) {
            return max($carry, strlen($item));
        }, 0) + 1;

        $message = sprintf('Deprecated WP Mock calls inside %s:', $this->testName);

        foreach ($this->getDeprecatedMethodsWithArgs() as $method => $args) {
            $firstRun = true;
            $extra = $maxLength - strlen($method);

            foreach ($args as $arg) {
                $message .= "\n  ";

                if ($firstRun) {
                    $message .= $method . str_repeat(' ', $extra);
                    $firstRun = false;
                    $extra = $maxLength;
                } else {
                    $message .= str_repeat(' ', $extra);
                }

                $message .= $arg;
            }
        }

        return $message;
    }

    /**
     * Gets a list of deprecated methods having been called.
     *
     * @return string[]
     */
    protected function getDeprecatedMethods(): array
    {
        $methods = [];

        foreach ($this->deprecatedCalls as $call) {
            $methods[] = $call[0];
        }

        return array_unique($methods);
    }

    /**
     * Gets a list of deprecated methods having been called, with their arguments formatted as JSON.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function getDeprecatedMethodsWithArgs(): array
    {
        $collection = [];

        foreach ($this->deprecatedCalls as $call) {
            $method = $call[0];
            $args = json_encode(array_map([$this, 'toScalar'], $call[1]));

            if (empty($collection[$method])) {
                $collection[$method] = [];
            }

            $collection[$method][] = $args;
        }

        return array_map('array_unique', $collection);
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
