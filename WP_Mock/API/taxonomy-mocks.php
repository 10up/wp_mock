<?php
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