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

		// The meaningful assertion is the shouldReceive() above,
		// here we just want to stop PHPUnit from complaining.
		$this->assertNull($listener->checkCalls());
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

		// The meaningful assertion is the shouldReceive() above,
		// here we just want to stop PHPUnit from complaining.
		$this->assertNull($listener->checkCalls());
	}

}
