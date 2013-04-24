<?php
/**
 * WP_Mock
 *
 * LICENSE
 *
 * Copyright 2013 10up and other contributors
 * http://10up.com
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package    WP_Mock
 * @copyright  Copyright (c) 2013 10up (http://10up.com)
 * @license    MIT License
 */

class WP_Mock {
	/**
	 * @var \WP_Mock\EventManager
	 */
	protected static $event_manager;

	/**
	 * @var \WP_Mock\Functions
	 */
	protected static $function_manager;

	/**
	 * Make sure Mockery doesn't have anything set up already.
	 */
	public static function setUp() {
		\Mockery::close();

		self::$event_manager = new \WP_Mock\EventManager();
		self::$function_manager = new \WP_Mock\Functions();
	}

	/**
	 * Tear down anything built up inside Mockery when we're ready to do so.
	 */
	public static function tearDown() {
		\Mockery::close();

		self::$event_manager->flush();
		self::$function_manager->flush();
	}

	/**
	 * Fire a specific (mocked) callback when an apply_filters() call is used.
	 *
	 * @param string $filter
	 *
	 * @return \WP_Mock\Filter
	 */
	public static function onFilter( $filter ) {
		return self::$event_manager->filter( $filter );
	}

	/**
	 * Fire a specific (mocked) callback when a do_action() call is used.
	 *
	 * @param string $action
	 *
	 * @return \WP_Mock\Action
	 */
	public static function onAction( $action ) {
		return self::$event_manager->action( $action );
	}

	/**
	 * Get a filter or action added callback object
	 *
	 * @param string $hook
	 * @param string $type
	 *
	 * @return \WP_Mock\HookedCallback
	 */
	public static function onHookAdded( $hook, $type = 'filter' ) {
		return self::$event_manager->callback( $hook, $type );
	}

	/**
	 * Get a filter added callback object
	 *
	 * @param string $hook
	 *
	 * @return \WP_Mock\HookedCallback
	 */
	public static function onFilterAdded( $hook ) {
		return self::onHookAdded( $hook, 'filter' );
	}

	/**
	 * Get an action added callback object
	 *
	 * @param string $hook
	 *
	 * @return \WP_Mock\HookedCallback
	 */
	public static function onActionAdded( $hook ) {
		return self::onHookAdded( $hook, 'action' );
	}

	/**
	 * Alert the Event Manager that an action has been invoked.
	 *
	 * @param string $action
	 */
	public static function invokeAction( $action ) {
		self::$event_manager->called( $action );
	}

	public static function addFilter( $hook ){
		self::addHook($hook, 'filter');
	}

	public static function addAction( $hook ){
		self::addHook($hook, 'action');
	}

	public static function addHook( $hook, $type = 'filter' ) {
		$type_name = "$type::$hook";
		self::$event_manager->called( $type_name, 'callback' );
	}

	/**
	 * Set up the expectation that an action will be called during the test.
	 *
	 * Mock a WordPress action, regardless of the parameters used.  This call merely
	 * verifies that the action is invoked by the tested method.
	 *
	 * @param string $action Action we expect the method to call
	 */
	public static function expectAction( $action ) {
		$intercept = \Mockery::mock( 'intercept' );
		$intercept->shouldReceive( 'intercepted' )->atLeast()->once();
		$args = func_get_args();
		$args = count($args) > 1 ? array_slice( $args, 1 ) : array( null );

		$action = self::onAction( $action );
		$responder = call_user_func_array( array( $action, 'with' ), $args );
		$responder->perform( array( $intercept, 'intercepted' ) );
	}

	public static function assertActionsCalled() {
		if ( ! self::$event_manager->allActionsCalled() ) {
			$failed = implode( ', ', self::$event_manager->expectedActions() );
			throw new PHPUnit_Framework_ExpectationFailedException( 'Method failed to invoke actions: ' . $failed, null );
		}
	}

	/**
	 * Add an expectation that an action should be added
	 *
	 * Really just a wrapper function for expectHookAdded()
	 *
	 * @param string $action   The action name
	 * @param string $callback The callback that should be registered
	 * @param int    $priority The priority it should be registered at
	 * @param int    $args     The number of arguments that should be allowed
	 */
	public static function expectActionAdded( $action, $callback, $priority = 10, $args = 1 ) {
		self::expectHookAdded( 'action', $action, $callback, $priority, $args );
	}

	/**
	 * Add an expectation that a filter should be added
	 *
	 * Really just a wrapper function for expectHookAdded()
	 *
	 * @param string $filter   The action name
	 * @param string $callback The callback that should be registered
	 * @param int    $priority The priority it should be registered at
	 * @param int    $args     The number of arguments that should be allowed
	 */
	public static function expectFilterAdded( $filter, $callback, $priority = 10, $args = 1 ) {
		self::expectHookAdded( 'filter', $filter, $callback, $priority, $args );
	}

	/**
	 * Add an expectation that a hook should be added
	 *
	 * @param string $type     The type of hook being added
	 * @param string $action   The action name
	 * @param string $callback The callback that should be registered
	 * @param int    $priority The priority it should be registered at
	 * @param int    $args     The number of arguments that should be allowed
	 */
	public static function expectHookAdded( $type, $action, $callback, $priority = 10, $args = 1 ) {
		$intercept = \Mockery::mock( 'intercept' );
		$intercept->shouldReceive( 'intercepted' )->atLeast()->once();

		self::onHookAdded( $action, $type )
			->with( $callback, $priority, $args )
			->perform( array( $intercept, 'intercepted' ) );
	}

	public static function assertHooksAdded() {
		if ( ! self:: $event_manager->allHooksAdded() ) {
			$failed = implode( ', ', self::$event_manager->expectedHooks() );
			throw new PHPUnit_Framework_ExpectationFailedException( 'Method failed to add hooks: ' . $failed, null );
		}
	}

	public static function wpFunction( $function_name, $arguments ) {
		self::$function_manager->register( $function_name, $arguments );
	}
}
