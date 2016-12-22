<?php

namespace WP_Mock;

use Mockery;
use PHPUnit_Framework_Assert;
use PHPUnit_Framework_RiskyTest;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;

class DeprecatedListenerTest extends PHPUnit_Framework_TestCase {

	/** @var DeprecatedListener */
	protected $object;

	protected function setUp() {
		$this->object = new DeprecatedListener();
	}

	public function tearDown() {
		$this->object->reset();
	}

	public function testLogDeprecatedCall() {
		$method = 'Foobar::asdf' . rand( 0, 9 );
		$args   = array( rand( 10, 99 ) );
		$this->object->logDeprecatedCall( $method, $args );

		$this->assertEquals( array( array( $method, $args ) ), $this->getCalls( $this->object ) );
	}

	public function testReset() {
		$this->object->logDeprecatedCall( 'Asdf', array( 'foobar' ) );
		$this->object->reset();

		$this->assertEquals( array(), $this->getCalls( $this->object ) );
	}

	public function testCheckCalls_scalar_only() {
		$this->object->logDeprecatedCall( 'FooBar::bazBat', array( 'string', true, 42 ) );
		$this->object->setTestName( 'TestName' );
		$testCase = Mockery::mock( 'PHPUnit_Framework_TestCase' );
		/** @var PHPUnit_Framework_TestCase $testCase */
		$this->object->setTestCase( $testCase );
		$result = Mockery::mock( 'PHPUnit_Framework_TestResult' );
		$result->shouldReceive( 'addFailure' )
			->once()
			->andReturnUsing( function ( $case, $exception, $int ) use ( $testCase ) {
				PHPUnit_Framework_Assert::assertSame( $testCase, $case );
				PHPUnit_Framework_Assert::assertTrue( $exception instanceof PHPUnit_Framework_RiskyTest );
				$message = <<<EOT
Deprecated WP Mock calls inside TestName:
  FooBar::bazBat ["string",true,42]
EOT;
				PHPUnit_Framework_Assert::assertEquals( $message, $exception->getMessage() );
				PHPUnit_Framework_Assert::assertTrue( 0 === $int );
			} );
		/** @var \PHPUnit_Framework_TestResult $result */
		$this->object->setTestResult( $result );

		$this->object->checkCalls();
	}

	protected function getCalls( $listener ) {
		$prop = new ReflectionProperty( $listener, 'calls' );
		$prop->setAccessible( true );

		return $prop->getValue( $listener );
	}

}
