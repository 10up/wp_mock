<?php
/**
 * Certain constants need to be mocked otherwise various WordPress functions will attempt
 * to include files that just don't exist.
 *
 * For example, nearly all uses of the WP_Http API require first including:
 *     ABSPATH . WPINC . '/class-http.php'
 *
 * If these constants are not set, and files do not exist at the location they specify,
 * functions referencing them will fatally err.
 *
 * The `! defined` check is used here so that individual test environments can override
 * the normal default by setting constants in a bootstrap configuration file.
 */

if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', __DIR__ . '/dummy-files' );
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '' );
}

if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', __DIR__ . '/dummy-files/wp-includes' );
}