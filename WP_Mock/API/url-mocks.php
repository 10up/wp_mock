<?php
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