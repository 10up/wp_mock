<?php

class DeprecatedMethodsTest extends \PHPUnit\Framework\TestCase {

	public function setUp() {
		WP_Mock::setUp();
		WP_Mock::getDeprecatedListener()->reset();
	}

	protected function tearDown() {
		WP_Mock::getDeprecatedListener()->reset();
		WP_Mock::tearDown();
	}

	public function testWpFunctionLogsDeprecationNotice() {
		$listener = WP_Mock::getDeprecatedListener();
		$result   = Mockery::mock( '\PHPUnit\Framework\TestResult' );
		$case     = Mockery::mock( '\PHPUnit\Framework\TestCase' );
		$listener->setTestCase( $case );
		$listener->setTestResult( $result );
		$result->shouldReceive( 'addFailure' )
			->once()
			->with( $case, Mockery::type( '\PHPUnit\Framework\RiskyTestError' ), 0 );
		WP_Mock::wpFunction( 'foobar' );
		$listener->checkCalls();
	}

	public function testWpPassthruFunctionLogsDeprecationNotice() {
		$listener = WP_Mock::getDeprecatedListener();
		$result   = Mockery::mock( '\PHPUnit\Framework\TestResult' );
		$case     = Mockery::mock( '\PHPUnit\Framework\TestCase' );
		$listener->setTestCase( $case );
		$listener->setTestResult( $result );
		$result->shouldReceive( 'addFailure' )
			->once()
			->with( $case, Mockery::type( '\PHPUnit\Framework\RiskyTestError' ), 0 );
		WP_Mock::wpPassthruFunction( 'foobar' );
		$listener->checkCalls();
	}

}
