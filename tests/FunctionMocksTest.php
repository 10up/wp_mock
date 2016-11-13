<?php

class FunctionMocksTest extends PHPUnit_Framework_TestCase {

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

	/**
	 * @runInSeparateProcess
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

}
