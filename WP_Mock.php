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
	 * Make sure Mockery doesn't have anything set up already.
	 */
	public static function setUp() {
		\Mockery::close();

		self::$event_manager = new \WP_Mock\EventManager();
	}

	/**
	 * Tear down anything built up inside Mockery when we're ready to do so.
	 */
	public static function tearDown() {
		\Mockery::close();

		self::$event_manager->flush();
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
	 * Alert the Event Manager that an action has been invoked.
	 *
	 * @param string $action
	 */
	public static function invokeAction( $action ) {
		self::$event_manager->called( $action );
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
		$intercept->shouldReceive( 'intercepted' );

		self::onAction( $action )->with( null )->perform( array( $intercept, 'intercepted' ) );
	}

	public static function assertActionsCalled() {
		if ( ! self::$event_manager->allActionsCalled() ) {
			$failed = implode( ', ', self::$event_manager->expectedActions() );
			throw new PHPUnit_Framework_ExpectationFailedException( 'Method failed to invoke actions: ' . $failed, null );
		}
	}
}