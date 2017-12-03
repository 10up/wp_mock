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

	protected function safe_offset( $value ) {
		if ( is_null( $value ) ) {
			return 'null';
		} elseif ( is_scalar( $value ) ) {
			return $value;
		} elseif ( is_object( $value ) ) {
			return spl_object_hash( $value );
		} elseif ( is_array( $value ) ) {
			$return = '';
			foreach ( $value as $k => $v ) {
				$k = is_numeric( $k ) ? '' : $k;
				$return .= $k . $this->safe_offset( $v );
			}

			return $return;
		}

		return '';
	}

	/** @return Action_Responder|Filter_Responder */
	public function with() {
		$args      = func_get_args();
		$responder = $this->new_responder();

		if ( $args === array( null ) ) {
			$this->processors['argsnull'] = $responder;
		} else {
			$num_args = count( $args );

			$processors = &$this->processors;
			for ( $i = 0; $i < $num_args - 1; $i ++ ) {
				$arg = $this->safe_offset( $args[ $i ] );

				if ( ! isset( $processors[ $arg ] ) ) {
					$processors[ $arg ] = array();
				}

				$processors = &$processors[ $arg ];
			}

			$processors[ $this->safe_offset( $args[ $num_args - 1 ] ) ] = $responder;
		}

		return $responder;
	}

	protected abstract function new_responder();

	/**
	 * Throw an exception if strict mode is on
	 *
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 */
	protected function strict_check() {
		if ( \WP_Mock::strictMode() ) {
			throw new \PHPUnit\Framework\ExpectationFailedException( $this->get_strict_mode_message() );
		}
	}

	/**
	 * @return string
	 */
	abstract protected function get_strict_mode_message();

}
