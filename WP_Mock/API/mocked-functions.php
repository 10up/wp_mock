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
	$args = array_slice( $args, 0 );

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
 * Register a taxonomy.
 *
 * @see \TaxonomyFactory
 *
 * @param string       $taxonomy
 * @param string       $object_type
 * @param string|array $args
 */
function register_taxonomy( $taxonomy, $object_type, $args = array() ) {
	\WP_Mock\Handler::handle_function( 'register_taxonomy', func_get_args() );
}

/**
 * Fetch a term
 *
 * @see \TermTaxonomyFactory
 *
 * @param int|object $term     The term object or term_id
 * @param string     $taxonomy
 * @param string     $object
 * @param string     $filter
 *
 * @return object|null The term object if it exists, otherwise null
 */
function get_term( $term, $taxonomy, $object = true, $filter = 'raw' ) {
	return \WP_Mock\Handler::handle_function( 'get_term', func_get_args() );
}

/**
 * Get a term by the specified field
 *
 * @see \TermTaxonomyFactory
 *
 * @param string     $field    Either 'slug', 'name', or 'id'
 * @param string|int $value    The value to search for
 * @param string     $taxonomy The taxonomy within which to search
 * @param string     $object
 * @param string     $filter
 *
 * @return mixed False if no term found, term object otherwise
 */
function get_term_by( $field, $value, $taxonomy, $object = true, $filter = 'raw' ) {
	return \WP_Mock\Handler::handle_function( 'get_term_by', func_get_args() );
}

/**
 * Get multiple terms according to arguments passed as second argument
 *
 * @see \TermTaxonomyFactory
 *
 * @param string       $taxonomy
 * @param string|array $args
 *
 * @return array
 */
function get_terms( $taxonomy, $args = '' ) {
	return \WP_Mock\Handler::handle_function( 'get_terms', func_get_args() );
}

/**
 * Check if a term exists for a given taxonomy
 *
 * @param int|string $term     Term id, slug, or name
 * @param string     $taxonomy
 * @param int        $parent
 *
 * @return int|array 0 if the term doesn't exist, term_id if the term exists and no taxonomy
 *                   was specified, or an associative array of term_id and term_taxonomy_id
 *                   if the term exists and a taxonomy was specified.
 */
function term_exists( $term, $taxonomy = '', $parent = 0 ) {
	return \WP_Mock\Handler::handle_function( 'term_exists', func_get_args() );
}

/**
 * Create a term for a taxonomy
 *
 * Possible keys for the $args array are:
 *  - 'alias_of'
 *  - 'description'
 *  - 'parent'
 *  - 'slug'
 *
 * @param string       $term     Term name
 * @param string       $taxonomy Taxonomy name
 * @param string|array $args     An array or param string of args for the term
 *
 * @return array
 */
function wp_insert_term( $term, $taxonomy, $args = array() ) {
	return \WP_Mock\Handler::handle_function( 'wp_insert_term', func_get_args() );
}

/**
 * Update an existing term for a taxonomy
 *
 * @param int    $term_id
 * @param string $taxonomy
 * @param array  $args
 *
 * @return array Returns term_id and term_taxonomy_id in an array on success
 */
function wp_update_term( $term_id, $taxonomy, $args = array() ) {
	return \WP_Mock\Handler::handle_function( 'wp_update_term', func_get_args() );
}

/**
 * Create term and taxonomy relationships
 *
 * Relates an object (post, link, etc.) to a term and taxonomy type. Creates a term and
 * taxonomy relationship if it doesn't already exist. Creates a term if it doesn't already
 * exist (using the slug).
 *
 * @param int              $object_id
 * @param array|int|string $terms     Slug(s) or ID(s) of the term(s)
 * @param string           $taxonomy
 * @param bool             $append    True to add relationship to existing, false to replace existing
 *
 * @return array The affected Term IDs
 */
function wp_set_object_terms( $object_id, $terms, $taxonomy, $append = false ) {
	return \WP_Mock\Handler::handle_function( 'wp_set_object_terms', func_get_args() );
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
 * Check if currently in the admin (includes ajax)
 *
 * @return bool Whether we are in the admin
 */
function is_admin() {
	return \WP_Mock\Handler::handle_function( 'is_admin', func_get_args() );
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

/**
 * Retrieve the url to the plugins directory or to a specific file within that directory.
 * You can hardcode the plugin slug in $path or pass __FILE__ as a second argument to get the correct folder name.
 *
 * @param string $path   Optional. Path relative to the plugins url.
 * @param string $plugin Optional. The plugin file that you want to be relative to - i.e. pass in __FILE__
 *
 * @return string Plugins url link with optional path appended.
 */
function plugins_url( $path = '', $plugin = '' ) {
	return \WP_Mock\Handler::handle_function( 'plugins_url', func_get_args() );
}

/**
 * Retrieve the url to the admin area for the current site.
 *
 * @param string $path   Optional path relative to the admin url.
 * @param string $scheme The scheme to use. Default is 'admin', which obeys force_ssl_admin() and is_ssl(). 'http' or 'https' can be passed to force those schemes.
 *
 * @return string Admin url link with optional path appended.
 */
function admin_url( $path = '', $scheme = 'admin' ) {
	return \WP_Mock\Handler::handle_function( 'admin_url', func_get_args() );
}



/**
 * Loads a plugin out of our shared plugins directory.
 *
 * @param string $plugin Plugin folder name (and filename) of the plugin
 * @param string $folder Optional. Folder to include from. Useful for when you have multiple themes and your own shared plugins folder.
 *
 * @return boolean True if the include was successful, false if it failed.
 */
function wpcom_vip_load_plugin( $plugin = false, $folder = 'plugins' ) {
	return \WP_Mock\Handler::handle_function( 'wpcom_vip_load_plugin', func_get_args() );
}

/**
 * For flash hosted elsewhere to work it looks for crossdomain.xml in
 * the host's * web root. If requested, this function echos
 * the crossdomain.xml file in the theme's root directory
 *
 * @author lloydbudd
 */
function vip_crossdomain_redirect() {
	return \WP_Mock\Handler::handle_function( 'vip_crossdomain_redirect', func_get_args() );
}

/**
 * Simple 301 redirects
 * array elements should be in the form of:
 * '/old' => 'http://wordpress.com/new/'
 *
 */
function vip_redirects( $vip_redirects_array = array(), $case_insensitive = false ) {
	return \WP_Mock\Handler::handle_function( 'vip_redirects', func_get_args() );
}