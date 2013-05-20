<?php
/**
 * Mock WordPress filters by substituting each filter with an advanced object
 * capable of intercepting calls and returning predictable data.
 *
 * @package    WP_Mock
 * @subpackage Hooks
 */

namespace WP_Mock;


class Filter extends Hook {
	/**
	 * Apply the stored filter.
	 *
	 * @param array $args Arguments passed to apply_filters()
	 *
	 * @return mixed
	 */
	public function apply( $args ) {
		if ( $args[0] === null ) {
			if ( isset( $this->processors['argsnull'] ) ) {
				return $this->processors['argsnull']->send();
			}
			return null;
		}
		$arg_num = count( $args );

		$processors = $this->processors;
		foreach ( $args as $arg ) {
			$key = $this->safe_offset( $arg );
			if ( ! isset( $processors[$key] ) ) {
				return $arg;
			}

			$processors = $processors[$key];
		}

		return $processors[$this->safe_offset( $args[$arg_num - 1] )]->send();
	}

	public function with() {
		$args = func_get_args();
		if ( ! isset( $args[0] ) || ( empty( $args[0] ) && ! is_string( $args[0] ) && ! is_int( $args[0] ) ) ) {
			$args = array( null );
		}
		return call_user_func_array( array( 'parent', 'with' ), $args );
	}

	protected function new_responder() {
		return new Filter_Responder();
	}

}

class Filter_Responder {
	/**
	 * @var mixed
	 */
	protected $value;

	public function reply( $value ) {
		$this->value = $value;
	}

	public function send() {
		return $this->value;
	}
}

