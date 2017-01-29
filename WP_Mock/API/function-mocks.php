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

/**
 * Returns the permalink to a post or page for use in PHP. It does NOT display the permalink and can be used outside of The Loop. On failure returns false.
 *
 * @since 1.0.0
 * 
 * @param id (mixed) (optional) The integer ID for a post or page, or a post object Default: The current post ID, when used in The Loop.
 * @return (string) The permalink URL.
 */
function get_permalink( $id ) {
	return \WP_Mock\Handler::handle_function( 'get_permalink', func_get_args() );
}

/**
 * TC refactored version of transition_post_status's WP core function (for async matter)
 * Hook for managing future post transitions to published.
 *
 * @since 2.3.0
 * 
 * @param $new_status (string) (required) New post status
 * @param $old_status (string) (required) Previous post status
 * @param $post (object) (required) Object type containing the post information
 * @return (void) This function does not return a value.
 */
function wp_async_task_transition_post_status( $new_status, $old_status, $post ) {
	return \WP_Mock\Handler::handle_function( 'wp_async_task_transition_post_status', func_get_args() );
}

/**
 * Retrieves a URL using the HTTP GET method, returning results in an array. Results include HTTP headers and content.
 *
 * @since 2.7.0
 * 
 * @param $url (string) (required) Universal Resource Locator (URL).
 * @param $args (array) (optional) Optional. See HTTP_API#Other_Arguments for argument details. 
 *        Note: If sending any array arguments (headers, cookies, etc.) then all of them must be included since array arguments are not "deep" merged.
 * @return Array of results including HTTP headers, WP_Error object on failure.
 */
function wp_remote_get( $url, $args = array() ) {
	return \WP_Mock\Handler::handle_function( 'wp_remote_get', func_get_args() );
}

/**
 * Retrieves the body of an already retrieved HTTP request.
 *
 * @since 2.7.0
 * 
 * @param $response (array) (required) HTTP response array from an already performed HTTP request.
 * @return a string. If there was an error returned by the existing HTTP request or a problem with the data then a blank string will be returned.
 */
function wp_remote_retrieve_body( $response ) {
	return \WP_Mock\Handler::handle_function( 'wp_remote_retrieve_body', func_get_args() );
}
