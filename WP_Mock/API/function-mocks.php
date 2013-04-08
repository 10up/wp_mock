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
	\WP_Mock::onActionAdded( $tag )->react( $function_to_add, (int) $priority, (int) $accepted_args );
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
	\WP_Mock::onFilterAdded( $tag )->react( $function_to_add, (int) $priority, (int) $accepted_args );
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
	$args = array_slice( $args, 1 );
	$args[0] = $value;

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
 * Kill WordPress execution and display HTML message with error message.
 *
 * This function complements the die() PHP function. The difference is that
 * HTML will be displayed to the user. It is recommended to use this function
 * only, when the execution should not continue any further. It is not
 * recommended to call this function very often and try to handle as many errors
 * as possible silently.
 *
 * @param string $message Error message.
 * @param string $title Error title.
 * @param string|array $args Optional arguments to control behavior.
 */
function wp_die( $message = '', $title = '', $args = array() ) {
	return \WP_Mock\Handler::handle_function( 'wp_die', func_get_args() );
}

/**
 * Retrieves the translation of $text. If there is no translation, or
 * the domain isn't loaded, the original text is returned.
 *
 * @see translate() An alias of translate()
 *
 * @param string $text Text to translate
 * @param string $domain Optional. Domain to retrieve the translated text
 * @return string Translated text
 */
function __( $text, $domain = 'default' ) {
	return \WP_Mock\Handler::handle_function( '__', func_get_args() );
}

/**
 * Makes sure that a user was referred from another admin page.
 *
 * To avoid security exploits.
 *
 * @param string $action Action nonce
 * @param string $query_arg where to look for nonce in $_REQUEST (since 2.5)
 */
function check_admin_referer( $action = -1, $query_arg = '_wpnonce' ) {
	return \WP_Mock\Handler::handle_function( 'check_admin_referer', func_get_args() );
}

/**
 * Whether current user has capability or role.
 *
 * @param string $capability Capability or role name.
 * @return bool
 */
function current_user_can( $capability ) {
	return \WP_Mock\Handler::handle_function( 'current_user_can', func_get_args() );
}

/**
 * Creates a random, one time use token.
 *
 * @param string|int $action Scalar value to add context to the nonce.
 * @return string The one use form token
 */
function wp_create_nonce( $action = -1 ) {
	return \WP_Mock\Handler::handle_function( 'wp_create_nonce', func_get_args() );
}

/**
 * Gets a WP_Theme object for a theme.
 *
 * @param string $stylesheet Directory name for the theme. Optional. Defaults to current theme.
 * @param string $theme_root Absolute path of the theme root to look in. Optional. If not specified, get_raw_theme_root()
 * 	is used to calculate the theme root for the $stylesheet provided (or current theme).
 * @return WP_Theme Theme object. Be sure to check the object's exists() method if you need to confirm the theme's existence.
 */
function wp_get_theme( $stylesheet = null, $theme_root = null ) {
	return \WP_Mock\Handler::handle_function( 'wp_get_theme', func_get_args() );
}

/**
 * Display settings errors registered by add_settings_error()
 *
 * Part of the Settings API. Outputs a <div> for each error retrieved by get_settings_errors().
 *
 * This is called automatically after a settings page based on the Settings API is submitted.
 * Errors should be added during the validation callback function for a setting defined in register_setting()
 *
 * The $sanitize option is passed into get_settings_errors() and will re-run the setting sanitization
 * on its current value.
 *
 * The $hide_on_update option will cause errors to only show when the settings page is first loaded.
 * if the user has already saved new values it will be hidden to avoid repeating messages already
 * shown in the default error reporting after submission. This is useful to show general errors like missing
 * settings when the user arrives at the settings page.
 *
 * @param string $setting Optional slug title of a specific setting who's errors you want.
 * @param boolean $sanitize Whether to re-sanitize the setting value before returning errors.
 * @param boolean $hide_on_update If set to true errors will not be shown if the settings page has already been submitted.
 */
function settings_errors( $setting = '', $sanitize = false, $hide_on_update = false ) {
	return \WP_Mock\Handler::handle_function( 'settings_errors', func_get_args() );
}

/**
 * Escaping for HTML blocks.
 *
 * @param string $text
 * @return string
 */
function esc_html( $text ) {
	return \WP_Mock\Handler::handle_function( 'esc_html', func_get_args() );
}

/**
 * Retrieve translated string with gettext context
 *
 * Quite a few times, there will be collisions with similar translatable text
 * found in more than two places but with different translated context.
 *
 * By including the context in the pot file translators can translate the two
 * strings differently.
 *
 * @param string $text Text to translate
 * @param string $context Context information for the translators
 * @param string $domain Optional. Domain to retrieve the translated text
 * @return string Translated context string without pipe
 */
function _x( $text, $context, $domain = 'default' ) {
	return \WP_Mock\Handler::handle_function( '_x', func_get_args() );
}

/**
 * Checks and cleans a URL.
 *
 * A number of characters are removed from the URL. If the URL is for displaying
 * (the default behaviour) ampersands are also replaced. The 'clean_url' filter
 * is applied to the returned cleaned URL.
 *
 * @param string $url The URL to be cleaned.
 * @param array $protocols Optional. An array of acceptable protocols.
 *		Defaults to 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn' if not set.
 * @param string $_context Private. Use esc_url_raw() for database usage.
 * @return string The cleaned $url after the 'clean_url' filter is applied.
 */
function esc_url( $url, $protocols = null, $_context = 'display' ) {
	return \WP_Mock\Handler::handle_function( 'esc_url', func_get_args() );
}

/**
 * Displays translated text that has been escaped for safe use in HTML output.
 *
 * @see translate() Echoes returned translate() string
 * @see esc_html()
 *
 * @param string $text Text to translate
 * @param string $domain Optional. Domain to retrieve the translated text
 */
function esc_html_e( $text, $domain = 'default' ) {
	return \WP_Mock\Handler::handle_function( 'esc_html_e', func_get_args() );
}

/**
 * Echos a submit button, with provided text and appropriate class
 *
 * @param string $text The text of the button (defaults to 'Save Changes')
 * @param string $type The type of button. One of: primary, secondary, delete
 * @param string $name The HTML name of the submit button. Defaults to "submit". If no id attribute
 *               is given in $other_attributes below, $name will be used as the button's id.
 * @param bool $wrap True if the output button should be wrapped in a paragraph tag,
 * 			   false otherwise. Defaults to true
 * @param array|string $other_attributes Other attributes that should be output with the button,
 *                     mapping attributes to their values, such as array( 'tabindex' => '1' ).
 *                     These attributes will be output as attribute="value", such as tabindex="1".
 *                     Defaults to no other attributes. Other attributes can also be provided as a
 *                     string such as 'tabindex="1"', though the array format is typically cleaner.
 */
function submit_button( $text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = null ) {
	return \WP_Mock\Handler::handle_function( 'submit_button', func_get_args() );
}

/**
 * Displays a form to the user to request for their FTP/SSH details in order to connect to the filesystem.
 * All chosen/entered details are saved, Excluding the Password.
 *
 * Hostnames may be in the form of hostname:portnumber (eg: wordpress.org:2467) to specify an alternate FTP/SSH port.
 *
 * Plugins may override this form by returning true|false via the <code>request_filesystem_credentials</code> filter.
 *
 * @param string $form_post the URL to post the form to
 * @param string $type the chosen Filesystem method in use
 * @param boolean $error if the current request has failed to connect
 * @param string $context The directory which is needed access to, The write-test will be performed on this directory by get_filesystem_method()
 * @param string $extra_fields Extra POST fields which should be checked for to be included in the post.
 * @return boolean False on failure. True on success.
 */
function request_filesystem_credentials( $form_post, $type = '', $error = false, $context = false, $extra_fields = null ) {
	return \WP_Mock\Handler::handle_function( 'request_filesystem_credentials', func_get_args() );
}

/**
 * Retrieve theme roots.
 *
 * @return array|string An array of theme roots keyed by template/stylesheet or a single theme root if all themes have the same root.
 */
function get_theme_root( $stylesheet_or_template = false ) {
	return \WP_Mock\Handler::handle_function( 'get_theme_root', func_get_args() );
}

/**
 * Initialises and connects the WordPress Filesystem Abstraction classes.
 * This function will include the chosen transport and attempt connecting.
 *
 * Plugins may add extra transports, And force WordPress to use them by returning the filename via the 'filesystem_method_file' filter.
 *
 * @param array $args (optional) Connection args, These are passed directly to the WP_Filesystem_*() classes.
 * @param string $context (optional) Context for get_filesystem_method(), See function declaration for more information.
 * @return boolean false on failure, true on success
 */
function WP_Filesystem( $args = false, $context = false ) {
	return \WP_Mock\Handler::handle_function( 'WP_Filesystem', func_get_args() );
}

/**
 * Whether Multisite support is enabled
 *
 * @return bool True if multisite is enabled, false otherwise.
 */
function is_multisite() {
	return \WP_Mock\Handler::handle_function( 'is_multisite', func_get_args() );
}

/**
 * Retrieve the url to the admin area for the network.
 *
 * @param string $path Optional path relative to the admin url.
 * @param string $scheme The scheme to use. Default is 'admin', which obeys force_ssl_admin() and is_ssl(). 'http' or 'https' can be passed to force those schemes.
 * @return string Admin url link with optional path appended.
 */
function network_admin_url( $path = '', $scheme = 'admin' ) {
	return \WP_Mock\Handler::handle_function( 'network_admin_url', func_get_args() );
}

/**
 * Retrieve a modified URL query string.
 *
 * You can rebuild the URL and append a new query variable to the URL query by
 * using this function. You can also retrieve the full URL with query data.
 *
 * Adding a single key & value or an associative array. Setting a key value to
 * an empty string removes the key. Omitting oldquery_or_uri uses the $_SERVER
 * value. Additional values provided are expected to be encoded appropriately
 * with urlencode() or rawurlencode().
 *
 * @param mixed $param1 Either newkey or an associative_array
 * @param mixed $param2 Either newvalue or oldquery or uri
 * @param mixed $param3 Optional. Old query or uri
 * @return string New URL query string.
 */
function add_query_arg() {
	return \WP_Mock\Handler::handle_function( 'add_query_arg', func_get_args() );
}

/**
 * Appends a trailing slash.
 *
 * Will remove trailing slash if it exists already before adding a trailing
 * slash. This prevents double slashing a string or path.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * @param string $string What to add the trailing slash to.
 * @return string String with trailing slash added.
 */
function trailingslashit( $string ) {
	return \WP_Mock\Handler::handle_function( 'trailingslashit', func_get_args() );
}

/**
 * Sanitizes a filename, replacing whitespace with dashes.
 *
 * Removes special characters that are illegal in filenames on certain
 * operating systems and special characters requiring special escaping
 * to manipulate at the command line. Replaces spaces and consecutive
 * dashes with a single dash. Trims period, dash and underscore from beginning
 * and end of filename.
 *
 * @param string $filename The filename to be sanitized
 * @return string The sanitized filename
 */
function sanitize_file_name( $filename ) {
	return \WP_Mock\Handler::handle_function( 'sanitize_file_name', func_get_args() );
}

/**
 * Register a settings error to be displayed to the user
 *
 * Part of the Settings API. Use this to show messages to users about settings validation
 * problems, missing settings or anything else.
 *
 * Settings errors should be added inside the $sanitize_callback function defined in
 * register_setting() for a given setting to give feedback about the submission.
 *
 * By default messages will show immediately after the submission that generated the error.
 * Additional calls to settings_errors() can be used to show errors even when the settings
 * page is first accessed.
 *
 * @param string $setting Slug title of the setting to which this error applies
 * @param string $code Slug-name to identify the error. Used as part of 'id' attribute in HTML output.
 * @param string $message The formatted message text to display to the user (will be shown inside styled <div> and <p>)
 * @param string $type The type of message it is, controls HTML class. Use 'error' or 'updated'.
 */
function add_settings_error( $setting, $code, $message, $type = 'error' ) {
	return \WP_Mock\Handler::handle_function( 'add_settings_error', func_get_args() );
}

/**
 * Enqueues script.
 *
 * Registers the script if src provided (does NOT overwrite) and enqueues.
 *
 * @param string $handle Script name
 * @param string $src Script url
 * @param array $deps (optional) Array of script names on which this script depends
 * @param string|bool $ver (optional) Script version (used for cache busting), set to null to disable
 * @param bool $in_footer (optional) Whether to enqueue the script before </head> or before </body>
 * @return null
 */
function wp_enqueue_script( $handle, $src = false, $deps = array(), $ver = false, $in_footer = false ) {
	return \WP_Mock\Handler::handle_function( 'wp_enqueue_script', func_get_args() );
}

/**
 * Wrapper for $wp_scripts->localize().
 *
 * Used to localize a script.
 * Works only if the script has already been added.
 * Accepts an associative array $l10n and creates JS object:
 * "$object_name" = {
 *   key: value,
 *   key: value,
 *   ...
 * }
 * See http://core.trac.wordpress.org/ticket/11520 for more information.
 *
 * @param string $handle The script handle that was registered or used in script-loader
 * @param string $object_name Name for the created JS object. This is passed directly so it should be qualified JS variable /[a-zA-Z0-9_]+/
 * @param array $l10n Associative PHP array containing the translated strings. HTML entities will be converted and the array will be JSON encoded.
 * @return bool Whether the localization was added successfully.
 */
function wp_localize_script( $handle, $object_name, $l10n ) {
	return \WP_Mock\Handler::handle_function( 'wp_localize_script', func_get_args() );
}

/**
 * Loads the plugin's translated strings.
 *
 * If the path is not given then it will be the root of the plugin directory.
 * The .mo file should be named based on the domain with a dash, and then the locale exactly.
 *
 * @param string $domain Unique identifier for retrieving translated strings
 * @param string $abs_rel_path Optional. Relative path to ABSPATH of a folder,
 * 	where the .mo file resides. Deprecated, but still functional until 2.7
 * @param string $plugin_rel_path Optional. Relative path to WP_PLUGIN_DIR. This is the preferred argument to use. It takes precedence over $abs_rel_path
 */
function load_plugin_textdomain( $domain, $abs_rel_path = false, $plugin_rel_path = false ) {
	return \WP_Mock\Handler::handle_function( 'load_plugin_textdomain', func_get_args() );
}

/**
 * Retrieve post meta field for a post.
 *
 * @since 1.5.0
 * @uses $wpdb
 * @link http://codex.wordpress.org/Function_Reference/get_post_meta
 *
 * @param int $post_id Post ID.
 * @param string $key Optional. The meta key to retrieve. By default, returns data for all keys.
 * @param bool $single Whether to return a single value.
 * @return mixed Will be an array if $single is false. Will be value of meta data field if $single
 *  is true.
 */
function get_post_meta( $post_id, $key = '', $single = false ) {
	return \WP_Mock\Handler::handle_function( 'get_post_meta', func_get_args() );
}

/**
 * Add meta data field to a post.
 *
 * Post meta data is called "Custom Fields" on the Administration Screen.
 *
 * @since 1.5.0
 * @uses $wpdb
 * @link http://codex.wordpress.org/Function_Reference/add_post_meta
 *
 * @param int $post_id Post ID.
 * @param string $meta_key Metadata name.
 * @param mixed $meta_value Metadata value.
 * @param bool $unique Optional, default is false. Whether the same key should not be added.
 * @return bool False for failure. True for success.
 */
function add_post_meta( $post_id, $meta_key, $meta_value, $unique = false ) {
	return \WP_Mock\Handler::handle_function( 'add_post_meta', func_get_args() );
}

/**
 * Update post meta field based on post ID.
 *
 * Use the $prev_value parameter to differentiate between meta fields with the
 * same key and post ID.
 *
 * If the meta field for the post does not exist, it will be added.
 *
 * @since 1.5.0
 * @uses $wpdb
 * @link http://codex.wordpress.org/Function_Reference/update_post_meta
 *
 * @param int $post_id Post ID.
 * @param string $meta_key Metadata key.
 * @param mixed $meta_value Metadata value.
 * @param mixed $prev_value Optional. Previous value to check before removing.
 * @return bool False on failure, true if success.
 */
function update_post_meta( $post_id, $meta_key, $meta_value, $prev_value = '' ) {
	return \WP_Mock\Handler::handle_function( 'update_post_meta', func_get_args() );
}

function setup_postdata( $post ) {
	return \WP_Mock\Handler::handle_function( 'setup_postdata', func_get_args() );
}

function wp_reset_postdata() {
	return \WP_Mock\Handler::handle_function( 'wp_reset_postdata', func_get_args() );
}

function wp_get_shortlink( $post_id ) {
	return \WP_Mock\Handler::handle_function( 'wp_get_shortlink', func_get_args() );
}

function wp_get_post_categories( $post_id ) {
	return \WP_Mock\Handler::handle_function( 'wp_get_post_categories', func_get_args() );
}

function get_posts() {
	return \WP_Mock\Handler::handle_function( 'get_posts', func_get_args() );
}

function get_page_by_title() {
	return \WP_Mock\Handler::handle_function( 'get_page_by_title', func_get_args() );
}

function wp_list_pluck() {
	return \WP_Mock\Handler::handle_function( 'wp_list_pluck', func_get_args() );
}
