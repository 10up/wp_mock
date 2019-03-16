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

	/**
	 * @runInSeparateProcess
	 */
	public function test_userFunction_returns_expectation() {
		WP_Mock::bootstrap();
		$this->assertInstanceOf(
			'\Mockery\ExpectationInterface',
			WP_Mock::userFunction( 'testWpMockFunction' )
		);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_assertHooksAdded_for_filters_and_actions() {
		WP_Mock::bootstrap();
		WP_Mock::expectFilterAdded( 'testFilter', 'testCallback' ,10, 1);
		WP_Mock::expectActionAdded( 'testAction', 'testCallback', 10, 1 );
		add_action( 'testAction', 'testCallback',10, 1 );
		add_filter('testFilter','testCallback', 10, 1);
		WP_Mock::assertHooksAdded();
		\Mockery::close();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_assertHooksAdded_for_filters_and_actions_fails() {
		WP_Mock::bootstrap();
		WP_Mock::expectFilterAdded( 'testFilter', 'testCallback', 10, 1 );
		WP_Mock::expectActionAdded( 'testAction', 'testCallback', 10, 1 );
		$this->expectException('PHPUnit\Framework\ExpectationFailedException');
		WP_Mock::assertHooksAdded();
		\Mockery::close();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_assertActionsCalled_actions() {
		WP_Mock::bootstrap();
		WP_Mock::expectAction( 'testAction' );
		do_action('testAction');
		WP_Mock::assertActionsCalled();
		\Mockery::close();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_assertActionsCalled_actions_fails() {
		WP_Mock::bootstrap();
		WP_Mock::expectAction( 'testAction' );
		$this->expectException( 'PHPUnit\Framework\ExpectationFailedException' );
		WP_Mock::assertActionsCalled();
		\Mockery::close();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_assertActionsCalled_filters() {

		WP_Mock::bootstrap();
		WP_Mock::expectFilter( 'testFilter','testVal' );
		apply_filters( 'testFilter','testVal' );
		WP_Mock::assertFiltersCalled();
		\Mockery::close();
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_assertActionsCalled_filters_fails() {

		WP_Mock::bootstrap();
		WP_Mock::expectFilter( 'testFilter2', 'testVal' );

		$this->expectException( 'Mockery\Exception\InvalidCountException' );
		\Mockery::close();
	}
}