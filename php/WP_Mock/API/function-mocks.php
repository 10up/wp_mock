<?php

if ( ! function_exists( 'add_action' ) ) {
	/**
	 * Hooks a function on to a specific action.
	 *
	 * Actions are the hooks that the WordPress core launches at specific points
	 * during execution, or when specific events occur. Plugins can specify that
	 * one or more of its PHP functions are executed at these points, using the
	 * Action API.
	 *
	 * @param string   $tag             The name of the action to which the $function_to_add is hooked.
	 * @param callback $function_to_add The name of the function you wish to be called.
	 * @param int      $priority        optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
	 * @param int      $accepted_args   optional. The number of arguments the function accept (default 1).
	 */
	function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		\WP_Mock::onActionAdded( $tag )->react( $function_to_add, (int) $priority, (int) $accepted_args );
	}
}

if ( ! function_exists( 'do_action' ) ) {
	/**
	 * Execute functions hooked on a specific action hook.
	 *
	 * @param string $tag     The name of the action to be executed.
	 * @param mixed  $arg,... Optional additional arguments which are passed on to the functions hooked to the action.
	 *
	 * @return null Will return null if $tag does not exist in $wp_filter array
	 */
	function do_action( $tag, $arg = '' ) {
		$args = func_get_args();
		$args = array_slice( $args, 1 );

		return \WP_Mock::onAction( $tag )->react( $args );
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	/**
	 * Dummy method to prevent filter hooks in constructor from failing.
	 */
	function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		\WP_Mock::onFilterAdded( $tag )->react( $function_to_add, (int) $priority, (int) $accepted_args );
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	/**
	 * Call the functions added to a filter hook.
	 *
	 * @param string $tag     The name of the filter hook.
	 * @param mixed  $value   The value on which the filters hooked to <tt>$tag</tt> are applied on.
	 * @param mixed  $var,... Additional variables passed to the functions hooked to <tt>$tag</tt>.
	 *
	 * @return mixed The filtered value after all hooked functions are applied to it.
	 */
	function apply_filters( $tag, $value ) {
		$args    = func_get_args();
		$args    = array_slice( $args, 1 );
		$args[0] = $value;

		return \WP_Mock::onFilter( $tag )->apply( $args );
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html() {
		return \WP_Mock\Handler::predefined_return_function_helper( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr() {
		return \WP_Mock\Handler::predefined_return_function_helper( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url() {
		return \WP_Mock\Handler::predefined_return_function_helper( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	function esc_url_raw() {
		return \WP_Mock\Handler::predefined_return_function_helper( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'esc_js' ) ) {
	function esc_js() {
		return \WP_Mock\Handler::predefined_return_function_helper( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'esc_textarea' ) ) {
	function esc_textarea() {
		return \WP_Mock\Handler::predefined_return_function_helper( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( '__' ) ) {
	function __() {
		return \WP_Mock\Handler::predefined_return_function_helper( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( '_e' ) ) {
	function _e() {
		\WP_Mock\Handler::predefined_echo_function_helper( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( '_x' ) ) {
	function _x() {
		return \WP_Mock\Handler::predefined_return_function_helper( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__() {
		return \WP_Mock\Handler::predefined_return_function_helper( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'esc_html_e' ) ) {
	function esc_html_e() {
		\WP_Mock\Handler::predefined_echo_function_helper( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'esc_html_x' ) ) {
	function esc_html_x() {
		return \WP_Mock\Handler::predefined_return_function_helper( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'esc_attr__' ) ) {
	function esc_attr__() {
		return \WP_Mock\Handler::predefined_return_function_helper( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'esc_attr_e' ) ) {
	function esc_attr_e() {
		\WP_Mock\Handler::predefined_echo_function_helper( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( 'esc_attr_x' ) ) {
	function esc_attr_x() {
		return \WP_Mock\Handler::predefined_return_function_helper( __FUNCTION__, func_get_args() );
	}
}

if ( ! function_exists( '_n' ) ) {
	function _n() {
		return \WP_Mock\Handler::predefined_return_function_helper( __FUNCTION__, func_get_args() );
	}
}
