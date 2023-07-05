<?php

namespace WP_Mock\Functions;

use Exception;
use Mockery\Mock;
use PHPUnit\Framework\ExpectationFailedException;
use WP_Mock;

/**
 * Functions handler.
 *
 * This internal handler is meant to handle predefined function calls.
 *
 * @see WP_Mock\Functions
 */
class Handler
{
    /**
     * Mocked method handlers registered by the test class.
     *
     * @var array<string, callable|callable-string|array<mixed>>
     */
    private static array $handlers = [];

    /**
     * Overrides any existing handlers to set a new callback.
     *
     * @param string|callable-string $function function name
     * @param callable|callable-string|array<mixed> $callback
     * @return void
     */
    public static function registerHandler(string $function, $callback): void
    {
        self::$handlers[$function] = $callback;
    }

    /**
     * Handles a mocked function call.
     *
     * @param string|callable-string $functionName function name
     * @param array<mixed> $args function arguments
     * @return mixed|null
     * @throws ExpectationFailedException
     */
    public static function handleFunction(string $functionName, array $args = [])
    {
        if (self::handlerExists($functionName)) {
            /** @var callable $callback */
            $callback = self::$handlers[$functionName];

            return call_user_func_array($callback, $args);
        } elseif (WP_Mock::strictMode()) {
            throw new ExpectationFailedException(sprintf('No handler found for function %s', $functionName));
        }

        return null;
    }

    /**
     * Checks if a handler exists.
     *
     * @param string|callable-string $functionName
     * @return bool
     */
    public static function handlerExists(string $functionName): bool
    {
        return isset(self::$handlers[$functionName]);
    }

    /**
     * Clears all registered handlers.
     *
     * @return void
     */
    public static function cleanup(): void
    {
        self::$handlers = [];
    }

    /**
     * Helper function for common passthru return functions.
     *
     * @param string $functionName function name
     * @param array<mixed> $args function args
     * @return scalar
     * @throws ExpectationFailedException
     */
    public static function handlePredefinedReturnFunction(string $functionName, array $args = [])
    {
        $result = self::handleFunction($functionName, $args);

        if (! self::handlerExists($functionName)) {
            $result = $args[0] ?? $result;
        }

        /** @var scalar $result */
        return $result;
    }

    /**
     * Helper function for common echo functions.
     *
     * @param string $functionName function name
     * @param array<mixed> $args function arguments
     * @return void
     * @throws Exception|ExpectationFailedException
     */
    public static function handlePredefinedEchoFunction(string $functionName, array $args = []): void
    {
        ob_start();

        try {
            self::handleFunction($functionName, $args);
        } catch (Exception $exception) {
            ob_end_clean();

            throw $exception;
        }

        $result = ob_get_clean();

        if (! is_string($result)) {
            throw new ExpectationFailedException(sprintf('Function %s did not echo a valid string', $functionName));
        }

        if (! self::handlerExists($functionName)) {
            /** @var scalar $result */
            $result = $args[0] ?? $result;
        }

        echo $result;
    }
}
