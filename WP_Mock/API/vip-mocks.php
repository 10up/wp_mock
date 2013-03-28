<?php
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