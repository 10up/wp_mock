<?php
/**
 * Hooks a function on to a specific action.
 *
 * Actions are the hooks that the WordPress core launches at specific points
 * during execution, or when specific events occur. Plugins can specify that
 * one or more of its PHP functions are executed at these points, using the
 * Action API.
 *
 * @param string   $tag                     The name of the action to which the $function_to_add is hooked.
 * @param callback $function_to_add         The name of the function you wish to be called.
 * @param int      $priority                optional. Used to specify the order in which the functions associated with a particular action are executed (default: 10). Lower numbers correspond with earlier execution, and functions with the same priority are executed in the order in which they were added to the action.
 * @param int      $accepted_args           optional. The number of arguments the function accept (default 1).
 */
function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
	\WP_Mock\Handler::handle_function( 'add_action', func_get_args() );
}

/**
 * Removes a function from a specified action hook.
 *
 * This function removes a function attached to a specified action hook. This
 * method can be used to remove default functions attached to a specific filter
 * hook and possibly replace them with a substitute.
 *
 *
 * @param string   $tag                The action hook to which the function to be removed is hooked.
 * @param callback $function_to_remove The name of the function which should be removed.
 * @param int      $priority           optional The priority of the function (default: 10).
 *
 * @return boolean Whether the function is removed.
 */
function remove_action( $tag, $function_to_remove, $priority = 10 ) {
	\WP_Mock\Handler::handle_function( 'remove_action', func_get_args() );
}

/**
 * Execute functions hooked on a specific action hook.
 *
 * @param string $tag     The name of the action to be executed.
 * @param mixed  $arg,... Optional additional arguments which are passed on to the functions hooked to the action.
 *
 * @return null Will return null if $tag does not exist in $wp_filter array
 */
function do_action( $tag, $arg = '') {
	$args = func_get_args();
	$args = array_slice( $args, 1 );

	return \WP_Mock::onAction( $tag )->react( $args );
}

/**
 * Dummy method to prevent filter hooks in constructor from failing.
 */
function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
	\WP_Mock\Handler::handle_function( 'add_filter', func_get_args() );
}

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
	$args = func_get_args();
	$args[1] = $value;
	$args = array_slice( $args, 1 );

	return \WP_Mock::onFilter( $tag )->apply( $args );
}

/**
 * Return a specifc unix timestamp.
 *
 * This returns a value from inside the class so the class itself can manage the tick.
 *
 * @return int
 */
function wp_nonce_tick() {
	return \WP_Mock\Handler::handle_function( 'wp_nonce_tick' );
}

/**
 * Generate a basic hash.
 *
 * @param string $data
 * @param string $scheme
 *
 * @return string
 */
function wp_hash( $data, $scheme = 'auth' ) {
	return \WP_Mock\Handler::handle_function( 'wp_hash', func_get_args() );
}

/**
 * Check if a variable is a WP_Error object
 *
 * @param mixed $thing
 *
 * @return bool True if it is an error, false if not
 */
function is_wp_error( $thing ) {
	return \WP_Mock\Handler::handle_function( 'is_wp_error', func_get_args() );
}

/**
 * Get a value from WordPress' cache, if the value has been set
 *
 * @param string $key
 * @param string $group
 * @param bool   $force
 * @param bool   &$found
 *
 * @return mixed
 */
function wp_cache_get( $key, $group = '', $force = false, &$found = null ) {
	return \WP_Mock\Handler::handle_function( 'wp_cache_get', func_get_args() );
}

/**
 * Set a value in the WordPress object cache
 *
 * @param string $key
 * @param mixed  $data
 * @param string $group
 * @param int    $expires
 */
function wp_cache_set( $key, $data, $group = '', $expires = 0 ) {
	\WP_Mock\Handler::handle_function( 'wp_cache_set', func_get_args() );
}

/**
 * Normally used to echo json to the browser in an ajax request
 *
 * @param mixed $response
 */
function wp_send_json( $response ) {
	\WP_Mock\Handler::handle_function( 'wp_send_json', func_get_args() );
}

/**
 * check for invalid UTF-8,
 * Convert single < characters to entity,
 * strip all tags,
 * remove line breaks, tabs and extra white space,
 * strip octets.
 *
 * @param string $str
 *
 * @return string
 */
function sanitize_text_field( $str ) {
	return \WP_Mock\Handler::handle_function( 'sanitize_text_field', func_get_args() );
}

/**
 * Register a custom post type.
 *
 * @param string $post_type
 * @param array  $args
 *
 * @return object|WP_Error The registered object or an error.
 */
function register_post_type( $post_type, $args ) {
	return \WP_Mock\Handler::handle_function( 'register_post_type', func_get_args() );
}

/**
 * Returns the absolute path to the directory of a theme's "template" files.
 *
 * In the case of a child theme, this is the absolute path to the directory
 * of the parent theme's files.
 *
 * @since 3.4.0
 * @access public
 *
 * @return string Absolute path of the template directory.
 */
function get_template_directory() {
	return \WP_Mock\Handler::handle_function( 'get_template_directory', func_get_args() );
}

/**
 * Add a new feed type like /atom1/.
 *
 * @since 2.1.0
 *
 * @param string   $feedname
 * @param callback $function Callback to run on feed display.
 *
 * @return string Feed action name.
 */
function add_feed( $feedname, $function ) {
	return \WP_Mock\Handler::handle_function( 'add_feed', func_get_args() );
}

/**
 * Add hook for shortcode tag.
 *
 * There can only be one hook for each shortcode. Which means that if another
 * plugin has a similar shortcode, it will override yours or yours will override
 * theirs depending on which order the plugins are included and/or ran.
 *
 * @param string   $tag  Shortcode tag to be searched for in post content
 * @param callable $func Hook to be run when shortcode is found
 */
function add_shortcode( $tag, $func ) {
	return \WP_Mock\Handler::handle_function( 'add_shortcode', func_get_args() );
}

/**
 * Removes hook for shortcode.
 *
 * @param string $tag Shortcode tag to remove hook for.
 */
function remove_shortcode( $tag ) {
	return \WP_Mock\Handler::handle_function( 'remove_shortcode', func_get_args() );
}

/**
 * Remove all shortcode tags from the given content.
 *
 * @param string $content Content to remove shortcode tags.
 * @return string Content without shortcode tags.
 */
function strip_shortcodes( $content ) {
	return \WP_Mock\Handler::handle_function( 'strip_shortcodes', func_get_args() );
}

/**
 * Retrieve option value based on name of option.
 *
 * If the option does not exist or does not have a value, then the return value
 * will be false. This is useful to check whether you need to install an option
 * and is commonly used during installation of plugin options and to test
 * whether upgrading is required.
 *
 * If the option was serialized then it will be unserialized when it is returned.
 *
 * @param string $option  Name of option to retrieve. Expected to not be SQL-escaped.
 * @param mixed  $default Default value to return if the option does not exist.
 *
 * @return mixed Value set for the option
 */
function get_option( $option, $default = false ) {
	return \WP_Mock\Handler::handle_function( 'get_option', func_get_args() );
}