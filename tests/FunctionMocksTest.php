<?php

class FunctionMocksTest extends PHPUnit_Framework_TestCase {

	/**
	 * @runInSeparateProcess
	 */
	public function testCommonFunctionsAreDefined() {
		$this->assertTrue( function_exists( 'esc_attr' ) );
		$this->assertTrue( function_exists( 'esc_html' ) );
		$this->assertTrue( function_exists( 'esc_js' ) );
		$this->assertTrue( function_exists( 'esc_textarea' ) );
		$this->assertTrue( function_exists( 'esc_url' ) );
		$this->assertTrue( function_exists( 'esc_url_raw' ) );
		$this->assertTrue( function_exists( '__' ) );
		$this->assertTrue( function_exists( '_e' ) );
		$this->assertTrue( function_exists( '_x' ) );
		$this->assertTrue( function_exists( 'esc_attr__' ) );
		$this->assertTrue( function_exists( 'esc_attr_e' ) );
		$this->assertTrue( function_exists( 'esc_attr_x' ) );
		$this->assertTrue( function_exists( 'esc_html__' ) );
		$this->assertTrue( function_exists( 'esc_html_e' ) );
		$this->assertTrue( function_exists( 'esc_html_x' ) );
		$this->assertTrue( function_exists( '_n' ) );
	}

}
