<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Eric
 * Date: 3/26/13
 * Time: 8:56 AM
 * To change this template use File | Settings | File Templates.
 */

namespace WP_Mock;

use Exception;
use PHPUnit\Framework\ExpectationFailedException;
use WP_Mock;

class Handler
{
    /**
     * Mocked method handlers registered by the test class.
     *
     * @var array
     */
    private static $handlers = array();

    /**
     * Overrides any existing handlers to set a new callback.
     *
     * @param string $function function name
     * @param string|array<mixed>|callable $callback
     * @return void
     */
    public static function register_handler(string $function, $callback): void
    {
        self::$handlers[$function] = $callback;
    }

    /**
     * Handle a mocked function call.
     *
     * @param string $functionName
     * @param array<mixed> $args
     * @return mixed
     * @throws ExpectationFailedException
     */
    public static function handle_function(string $functionName, array $args = [])
    {
        if (self::handler_exists($functionName)) {
            $callback = self::$handlers[$functionName];

            return call_user_func_array($callback, $args);
        } elseif (WP_Mock::strictMode()) {
            throw new ExpectationFailedException(sprintf('No handler found for %s', $functionName));
        }

        return null;
    }

    /**
     * Check if a handler exists
     *
     * @param string $function_name
     *
     * @return bool
     */
    public static function handler_exists($function_name)
    {
        return isset(self::$handlers[ $function_name ]);
    }

    /**
     * Clear all registered handlers.
     */
    public static function cleanup()
    {
        self::$handlers = array();
    }

    /**
     * Helper function for common passthru return functions.
     *
     * @param string $functionName
     * @param array<mixed>  $args
     * @return ?mixed
     * @throws ExpectationFailedException
     */
    public static function predefined_return_function_helper(string $functionName, array $args = [])
    {
        $result = self::handle_function($functionName, $args);

        if (! self::handler_exists($functionName)) {
            $result = $args[0] ?? $result;
        }

        return $result;
    }

    /**
     * Helper function for common echo functions.
     *
     * @param string $functionName
     * @param array<int, string>  $args
     * @return void
     * @throws Exception|ExpectationFailedException
     */
    public static function predefined_echo_function_helper(string $functionName, array $args = []): void
    {
        ob_start();

        try {
            self::handle_function($functionName, $args);
        } catch (Exception $exception) {
            ob_end_clean();
            /** @phpstan-ignore-next-line */
            throw $exception;
        }

        $result = ob_get_clean() ?: '';

        if (! self::handler_exists($functionName)) {
            $result = $args[0] ?? $result;
        }

        echo $result;
    }
}
