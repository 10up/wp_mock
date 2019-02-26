<?php

class FunctionMocksTest extends \PHPUnit\Framework\TestCase {

	private $common_functions = array(
		'esc_attr',
		'esc_html',
		'esc_js',
		'esc_textarea',
		'esc_url',
		'esc_url_raw',
		'__',
		'_e',
		'_x',
		'esc_attr__',
		'esc_attr_e',
		'esc_attr_x',
		'esc_html__',
		'esc_html_e',
		'esc_html_x',
		'_n',
	);

	protected function setUp() : void {
		if ( ! $this->isInIsolation() ) {
			WP_Mock::setUp();
		}
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testCommonFunctionsAreDefined() {
		// First we assert that all common functions get removed from the returned array. If any one of these functions
		// doesn't get removed, that means it already exists.
		$this->assertEmpty( array_filter( $this->common_functions, 'function_exists' ) );
		WP_Mock::bootstrap();
		// Now we assert that the array doesn't lose any items after bootstrap, meaning all expected functions got
		// defined correctly.
		$this->assertEquals( $this->common_functions, array_filter( $this->common_functions, 'function_exists' ) );
	}

	/**
	 * @dataProvider dataCommonFunctionsDefaultFunctionality
	 */
	public function testCommonFunctionsDefaultFunctionality( $function, $action ) {
		$input = $expected = 'Something Random ' . rand( 0, 99 );
		if ( 'echo' === $action ) {
			$this->expectOutputString( $input );
			$expected = null;
		}
		$this->assertEquals( $expected, call_user_func( $function, $input ) );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testDefaultFailsInStrictMode() {
		$this->expectExceptionMessageRegExp('/No handler found for \w+/');
		$this->expectException('\PHPUnit\Framework\ExpectationFailedException');
		WP_Mock::activateStrictMode();
		WP_Mock::bootstrap();
		_e('Test');
	}

	public function dataCommonFunctionsDefaultFunctionality() {
		return array_map( function ( $function ) {
			return array( $function, '_e' === substr( $function, - 2 ) ? 'echo' : 'return' );
		}, $this->common_functions );
	}

	public function testMockingOverridesDefaults() {
		$this->assertEquals( 'Input', __( 'Input' ) );
		WP_Mock::userFunction( '__' )->andReturn( 'Output' );
		$this->assertEquals( 'Output', __( 'Input' ) );
	}

	public function testBotchedMocksStillOverrideDefault() {
		WP_Mock::userFunction( 'esc_html' );
		$this->assertEmpty( esc_html( 'Input' ) );
	}

}
