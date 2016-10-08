<?php

class WP_MockTest extends PHPUnit_Framework_TestCase {

	/**
	 * @runInSeparateProcess
	 */
	public function test_strictMode_off_by_default() {
		WP_Mock::bootstrap();
		$this->assertFalse( WP_Mock::strictMode() );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_activateStrictMode_turns_strict_mode_on() {
		WP_Mock::activateStrictMode();
		$this->assertTrue( WP_Mock::strictMode() );
	}

	public function test_userFunction_returns_expectation() {
		$this->assertInstanceOf(
			'\Mockery\ExpectationInterface',
			WP_Mock::userFunction( 'testWpMockFunction' )
		);
	}

}
