<?php

class WP_MockTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @runInSeparateProcess
	 */
	public function test_strictMode_off_by_default() {
		$this->assertFalse( WP_Mock::strictMode() );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_activateStrictMode_turns_strict_mode_on() {
		WP_Mock::activateStrictMode();
		$this->assertTrue( WP_Mock::strictMode() );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_activateStrictMode_does_not_work_after_bootstrap() {
		WP_Mock::bootstrap();
		WP_Mock::activateStrictMode();
		$this->assertFalse( WP_Mock::strictMode() );
	}

	public function test_userFunction_returns_expectation() {
		WP_Mock::bootstrap();
		$this->assertInstanceOf(
			'\Mockery\ExpectationInterface',
			WP_Mock::userFunction( 'testWpMockFunction' )
		);
	}

}
