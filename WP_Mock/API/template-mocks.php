<?php
/**
 * Check if currently in the admin (includes ajax)
 *
 * @return bool Whether we are in the admin
 */
function is_admin() {
	return \WP_Mock\Handler::handle_function( 'is_admin', func_get_args() );
}