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
		if ( isset( self::$handlers[ $function_name ] ) ) {
			$callback = self::$handlers[ $function_name ];

			return call_user_func_array( $callback, $args );
		}
	}

	/**
	 * Clear all registered handlers.
	 */
	public static function cleanup() {
		self::$handlers = array();
	}
}