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
     * @var callable[]|callable-string[]
     */
    private static array $handlers = [];

    /**
     * Overrides any existing handlers to set a new callback.
     *
     * @param callable-string $function function name
     * @param callable|callable-string $callback
     * @return void
     */
    public static function registerHandler(string $function, $callback): void
    {
        self::$handlers[$function] = $callback;
    }

    /**
     * Handles a mocked function call.
     *
     * @param string $functionName function name
     * @param array<mixed> $args function arguments
     * @return mixed
     * @throws ExpectationFailedException
     */
    public static function handleFunction(string $functionName, array $args = [])
    {
        if (self::handlerExists($functionName)) {
            $callback = self::$handlers[$functionName];

            return call_user_func_array($callback, $args);
        } elseif (WP_Mock::strictMode()) {
            throw new ExpectationFailedException(sprintf('No handler found for %s', $functionName));
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
     * @return mixed
     * @throws ExpectationFailedException
     */
    public static function handlePredefinedReturnFunction(string $functionName, array $args = [])
    {
        $result = self::handleFunction($functionName, $args);

        if (! self::handlerExists($functionName)) {
            $result = $args[0] ?? $result;
        }

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

        $result = ob_get_clean() ?: '';

        if (! self::handlerExists($functionName)) {
            /** @var scalar $result */
            $result = $args[0] ?? $result;
        }

        echo $result;
    }
}
