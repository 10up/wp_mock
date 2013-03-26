<?php
/**
 * Mock WordPress filters by substituting each filter with an advanced object
 * capable of intercepting calls and returning predictable data.
 *
 * @package WP_Mock
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
		$arg_num = count( $args );

		$processors = $this->processors;
		for( $i = 0; $i < $arg_num - 1; $i++ ) {
			$arg = $args[ $i ];

			if ( ! isset( $processors[ $arg ] ) ) {
				return func_get_arg( 0 );
			}

			$processors = $processors[ $arg ];
		}

		return $processors[ $args[ $arg_num - 1 ] ]->send();
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