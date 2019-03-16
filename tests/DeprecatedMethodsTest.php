<?php

class DeprecatedMethodsTest extends \PHPUnit\Framework\TestCase {

	public function setUp() : void {
		WP_Mock::setUp();
		WP_Mock::getDeprecatedListener()->reset();
	}

	protected function tearDown() : void {
		WP_Mock::getDeprecatedListener()->reset();
		WP_Mock::tearDown();
	}

	public function testWpFunctionLogsDeprecationNotice() {
		$listener = WP_Mock::getDeprecatedListener();
		$testResult = new \PHPUnit\Framework\TestResult();
		$result   = Mockery::mock( $testResult );
		$case     = Mockery::mock( '\PHPUnit\Framework\TestCase' );
		$listener->setTestCase( $case );
		$listener->setTestResult( $result );
		$result->shouldReceive( 'addFailure' )
			->once()
			->with( $case, Mockery::type( '\PHPUnit\Framework\RiskyTestError' ), 0 );
		WP_Mock::wpFunction( 'foobar' );
		$this->assertNull($listener->checkCalls());
	}

	public function testWpPassthruFunctionLogsDeprecationNotice() {
		$listener = WP_Mock::getDeprecatedListener();
		$testResult = new \PHPUnit\Framework\TestResult();
		$result = Mockery::mock( $testResult );
		$case     = Mockery::mock( '\PHPUnit\Framework\TestCase' );
		$listener->setTestCase( $case );
		$listener->setTestResult( $result );
		$result->shouldReceive( 'addFailure' )
			->once()
			->with( $case, Mockery::type( '\PHPUnit\Framework\RiskyTestError' ), 0 );
		WP_Mock::wpPassthruFunction( 'foobar' );
		$this->assertNull( $listener->checkCalls() );
	}

}
