<?php

namespace WP_Mock\Tools;

use WP_Mock;
use Mockery;
use Exception;
use ReflectionMethod;
use WP_Mock\Tools\Constraints\ExpectationsMet;
use WP_Mock\Tools\Constraints\IsEqualHtml;

abstract class TestCase extends \PHPUnit_Framework_TestCase {

	protected $mockedStaticMethods = array();

	/**
	 * @var array
	 */
	protected $__default_post = array();

	/**
	 * @var array
	 */
	protected $__default_get = array();

	/**
	 * @var array
	 */
	protected $__default_request = array();

	/**
	 * @var bool|callable
	 */
	protected $__contentFilterCallback = false;

	public function setUp() {
		WP_Mock::setUp();

		$_GET     = (array) $this->__default_get;
		$_POST    = (array) $this->__default_post;
		$_REQUEST = (array) $this->__default_request;

		$this->__contentFilterCallback = false;

		$annotations = $this->getAnnotations();
		if (
			! isset( $annotations['stripTabsAndNewlinesFromOutput'] ) ||
			$annotations['stripTabsAndNewlinesFromOutput'][0] !== 'disabled' ||
			(
				is_numeric( $annotations['stripTabsAndNewlinesFromOutput'][0] ) &&
				(int) $annotations['stripTabsAndNewlinesFromOutput'][0] !== 0
			)
		) {
			$this->__contentFilterCallback = array( $this, 'stripTabsAndNewlines' );
			$this->setOutputCallback( $this->__contentFilterCallback );
		}

		$this->cleanGlobals();
	}

	public function tearDown() {
		WP_Mock::tearDown();

		$this->cleanGlobals();

		$this->mockedStaticMethods = array();

		$_GET     = array();
		$_POST    = array();
		$_REQUEST = array();
	}

	public function assertActionsCalled() {
		$actions_not_added = $expected_actions = 0;
		try {
			WP_Mock::assertActionsCalled();
		} catch ( Exception $e ) {
			$actions_not_added = 1;
			$expected_actions  = $e->getMessage();
		}
		$this->assertEmpty( $actions_not_added, $expected_actions );
	}

	public function assertHooksAdded() {
		$hooks_not_added = $expected_hooks = 0;
		try {
			WP_Mock::assertHooksAdded();
		} catch ( Exception $e ) {
			$hooks_not_added = 1;
			$expected_hooks  = $e->getMessage();
		}
		$this->assertEmpty( $hooks_not_added, $expected_hooks );
	}

	public function stripTabsAndNewlines( $content ) {
		return str_replace( array( "\t", "\r", "\n" ), '', $content );
	}

	public function expectOutputString( $expectedString ) {
		if ( is_callable( $this->__contentFilterCallback ) ) {
			$expectedString = call_user_func( $this->__contentFilterCallback, $expectedString );
		}
		parent::expectOutputString( $expectedString );
	}

	public function assertCurrentConditionsMet( $message = '' ) {
		$this->assertThat( null, new ExpectationsMet, $message );
	}

	public function assertConditionsMet( $message = '' ) {
		$this->assertCurrentConditionsMet( $message );
	}

	public function assertEqualsHTML( $expected, $actual, $message = '' ) {
		$constraint = new IsEqualHtml( $expected );
		$this->assertThat( $actual, $constraint, $message );
	}

	/**
	 * Mock a static method of a class
	 *
	 * @param string      $class  The classname or class::method name
	 * @param null|string $method The method name. Optional if class::method used for $class
	 *
	 * @return \Mockery\Expectation
	 * @throws Exception
	 */
	protected function mockStaticMethod( $class, $method = null ) {
		if ( ! $method ) {
			list( $class, $method ) = ( explode( '::', $class ) + array( null, null ) );
		}
		if ( ! $method ) {
			throw new Exception( sprintf( 'Could not mock %s::%s', $class, $method ) );
		}
		if ( ! WP_Mock::usingPatchwork() || ! function_exists( 'Patchwork\Interceptor\patch' ) ) {
			throw new Exception( 'Patchwork is not loaded! Please load patchwork before mocking static methods!' );
		}

		$safe_method = "wp_mock_safe_$method";
		$signature   = md5( "$class::$method" );
		if ( ! empty( $this->mockedStaticMethods[$signature] ) ) {
			$mock = $this->mockedStaticMethods[$signature];
		} else {

			$rMethod = false;
			if ( class_exists( $class ) ) {
				$rMethod = new ReflectionMethod( $class, $method );
			}
			if (
				$rMethod &&
				(
					! $rMethod->isUserDefined() ||
					! $rMethod->isStatic() ||
					$rMethod->isPrivate()
				)
			) {
				throw new Exception( sprintf( '%s::%s is not a user-defined non-private static method!', $class, $method ) );
			}

			/** @var \Mockery\Mock $mock */
			$mock = Mockery::mock( $class );
			$mock->shouldAllowMockingProtectedMethods();
			$this->mockedStaticMethods[$signature] = $mock;

			\Patchwork\Interceptor\patch( "$class::$method", function () use ( $mock, $safe_method ) {
				return call_user_func_array( array( $mock, $safe_method ), func_get_args() );
			}, ! ( $rMethod ) );
		}
		$expectation = $mock->shouldReceive( $safe_method );

		return $expectation;
	}

	protected function cleanGlobals() {
		$common_globals = array(
			'post',
			'wp_query',
		);
		foreach ( $common_globals as $var ) {
			if ( isset( $GLOBALS[$var] ) ) {
				unset( $GLOBALS[$var] );
			}
		}

	}

}

