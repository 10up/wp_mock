<?php

class DeprecatedMethodsTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		WP_Mock::setUp();
		WP_Mock::getDeprecatedListener()->reset();
	}

	protected function tearDown() {
		WP_Mock::getDeprecatedListener()->reset();
		WP_Mock::tearDown();
	}

	public function testWpFunctionLogsDeprecationNotice() {
		$rand     = rand( 1, 999 );
		$listener = WP_Mock::getDeprecatedListener();
		$result   = Mockery::mock( 'PHPUnit_Framework_TestResult' );
		$case     = Mockery::mock( 'PHPUnit_Framework_TestCase' );
		$listener->setTestCase( $case );
		$listener->setTestResult( $result );
		$result->shouldReceive( 'addFailure' )
			->once()
			->with( $case, Mockery::type( 'PHPUnit_Framework_RiskyTestError' ), 0 );
		WP_Mock::wpFunction( 'foobar', array( 'return' => $rand ) );
		$listener->checkCalls();
	}

}
