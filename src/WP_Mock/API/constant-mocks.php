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

/**
 * @since 0.71
 */
if ( ! defined( 'EZSQL_VERSION' ) ) {
	define( 'EZSQL_VERSION', 'WP1.25' );
}

/**
 * HHVM does not support case-insensitive constants.
 * 
 * @since 0.71
 * @see   http://hhvm.com/blog/3095/getting-wordpress-running-on-hhvm
 */
if ( ! defined( 'OBJECT' ) ) {
	define( 'OBJECT', 'OBJECT' );
}

if ( ! defined( 'Object' ) ) {
	define( 'Object', 'OBJECT' );
}

if ( ! defined( 'object' ) ) {
	define( 'object', 'OBJECT' );
}

/**
 * @since 2.5.0
 */
if ( ! defined( 'OBJECT_K' ) ) {
	define( 'OBJECT_K', 'OBJECT_K' );
}

/**
 * @since 0.71
 */
if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

/**
 * @since 0.71
 */
if ( ! defined( 'ARRAY_N' ) ) {
	define( 'ARRAY_N', 'ARRAY_N' );
}
