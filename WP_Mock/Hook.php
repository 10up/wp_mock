<?php
/**
 * Abstract Hook interface for both actions and filters.
 *
 * @package WP_Mock
 * @subpackage Hooks
 */

namespace WP_Mock;


abstract class Hook {
	/** @var string Hook name */
	protected $name;

	/** @var array Collection of processors */
	protected $processors = array();

	public function __construct( $name ) {
		$this->name = $name;
	}

	public function with() {
		$args = func_get_args();
		$num_args = count( $args );

		$processors = &$this->processors;
		for( $i = 0; $i < $num_args - 1; $i++ ) {
			$arg = $args[ $i ];

			if ( ! isset( $processors[ $arg ] ) ) {
				$processors[ $arg ] = array();
			}

			$processors = $processors[ $arg ];
		}

		$responder = $this->new_responder();
		$processors[ $args[ $num_args - 1 ] ] = $responder;

		return $responder;
	}

	protected abstract function new_responder();
}