<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Eric
 * Date: 3/26/13
 * Time: 8:56 AM
 * To change this template use File | Settings | File Templates.
 */

namespace WP_Mock;


class Handler {
	/**
	 * Mocked method handlers registered by the test class.
	 *
	 * @var array
	 */
	private static $handlers = array();

	/**
	 * Overrides any existing handlers to set a new callback.
	 *
	 * @param string $function_name
	 * @param string $callback
	 */
	public static function register_handler( $function_name, $callback ) {
		self::$handlers[ $function_name ] = $callback;
	}

	/**
	 * Handle a mocked function call.
	 *
	 * @param string $function_name
	 * @param array  $args
	 *
	 * @return mixed
	 */
	public static function handle_function( $function_name, $args = array() ) {
		if ( self::handler_exists( $function_name ) ) {
			$callback = self::$handlers[ $function_name ];

			return call_user_func_array( $callback, $args );
		} elseif ( \WP_Mock::strictMode() ) {
			throw new \PHPUnit\Framework\ExpectationFailedException(
				sprintf( 'No handler found for %s', $function_name )
			);
		}
	}

	/**
	 * Check if a handler exists
	 *
	 * @param string $function_name
	 *
	 * @return bool
	 */
	public static function handler_exists( $function_name ) {
		return isset( self::$handlers[ $function_name ] );
	}

	/**
	 * Clear all registered handlers.
	 */
	public static function cleanup() {
		self::$handlers = array();
	}

	/**
	 * Helper function for common passthru return functions
	 *
	 * @param string $function_name
	 * @param array  $args
	 *
	 * @return mixed
	 */
	public static function predefined_return_function_helper( $function_name, array $args ) {
		$result = self::handle_function( $function_name, $args );
		if ( ! self::handler_exists( $function_name ) ) {
			$result = isset( $args[0] ) ? $args[0] : $result;
		}

		return $result;
	}

	/**
	 * Helper function for common echo functions
	 *
	 * @param string $function_name
	 * @param array  $args
	 *
	 * @throws \Exception
	 */
	public static function predefined_echo_function_helper( $function_name, array $args ) {
		ob_start();
		try {
			self::handle_function( $function_name, $args );
		} catch ( \Exception $exception ) {
			ob_end_clean();
			throw $exception;
		}
		$result = ob_get_clean();
		if ( ! self::handler_exists( $function_name ) ) {
			$result = isset( $args[0] ) ? $args[0] : $result;
		}

		echo $result;
	}

}
