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
 * @package    Mockery
 * @copyright  Copyright (c) 2013 10up (http://10up.com)
 * @license    MIT License
 */

class WP_Mock {
	protected static $filters = array();

	/**
	 * Make sure Mockery doesn't have anything set up already.
	 */
	public static function setUp() {
		\Mockery::close();
	}

	/**
	 * Tear down anything built up inside Mockery when we're ready to do so.
	 */
	public static function tearDown() {
		\Mockery::close();

		self::$filters = array();
	}

	public static function onFilter( $filter ) {
		if ( ! isset( self::$filters[ $filter ] ) ) {
			self::$filters[ $filter ] = new \WP_Mock\Filter( $filter );
		}

		return self::$filters[ $filter ];
	}
}