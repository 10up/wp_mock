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
		if ( $args[0] === null && count( $args ) === 1 ) {
			if ( isset( $this->processors['argsnull'] ) ) {
				return $this->processors['argsnull']->send();
			}
			$this->strict_check();

			return null;
		}

		$processors = $this->processors;
		foreach ( $args as $arg ) {
			$key = $this->safe_offset( $arg );
			if ( ! is_array( $processors ) || ! isset( $processors[ $key ] ) ) {
				$this->strict_check();

				return $arg;
			}

			$processors = $processors[ $key ];
		}

		return call_user_func_array( array($processors, 'send'), $args );
	}

	protected function new_responder() {
		return new Filter_Responder();
	}

	/**
	 * @return string
	 */
	protected function get_strict_mode_message() {
		return sprintf( 'Unexpected use of apply_filters for filter %s', $this->name );
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
		if ( $this->value instanceof InvokedFilterValue ) {
			return call_user_func_array( $this->value, func_get_args() );
		}

		return $this->value;
	}
}

