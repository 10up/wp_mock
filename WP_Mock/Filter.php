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
		foreach( $args as $arg ) {
			$key = $this->hash_key( $arg );
			if ( ! isset( $processors[ $key ] ) ) {
				return $arg;
			}

			$processors = $processors[ $key ];
		}

		return $processors[ $this->hash_key( $args[ $arg_num - 1 ] ) ]->send();
	}

	protected function new_responder() {
		return new Filter_Responder();
	}

	protected function hash_key( $arg ) {
		return md5( serialize( $arg ) );
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