<?php

namespace WP_Mock;

use PHPUnit_Framework_TestCase;
use ReflectionProperty;

class DeprecatedListenerTest extends PHPUnit_Framework_TestCase {

	/** @var DeprecatedListener */
	protected $object;

	protected function setUp() {
		$this->object = new DeprecatedListener();
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

	protected function getCalls( $listener ) {
		$prop = new ReflectionProperty( $listener, 'calls' );
		$prop->setAccessible( true );

		return $prop->getValue( $listener );
	}

}
