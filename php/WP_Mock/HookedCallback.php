<?php

namespace WP_Mock;

class HookedCallback extends Hook {

	public function react( $callback, $priority, $argument_count ) {
		\WP_Mock::addHook( $this->name );

		$safe_callback = $this->safe_offset( $callback );
		if (
			empty( $this->processors[ $safe_callback ] ) ||
			empty( $this->processors[ $safe_callback ][ $priority ] ) ||
			empty( $this->processors[ $safe_callback ][ $priority ][ $argument_count ] )
		) {
			$this->strict_check( $callback );

			return null;
		}

		return $this->processors[ $this->safe_offset( $callback ) ][ $priority ][ $argument_count ]->react();
	}

	protected function new_responder() {
		return new HookedCallbackResponder();
	}

	protected function safe_offset( $value ) {
		if ( $value instanceof \Closure ) {
			$value = '__CLOSURE__';
		}
		return parent::safe_offset( $value );
	}

	/**
	 * Converts a callable to a string
	 *
	 * Closures get returned as 'Closure', objects (those with an __invoke() method get turned into <Class>::__invoke,
	 * and arrays get turned into <Class>::<method>
	 *
	 * @param callable $callback
	 *
	 * @return string
	 */
	protected function callback_to_string( $callback ) {
		if ( ! is_string( $callback ) ) {
			if ( $callback instanceof \Closure ) {
				$callback = 'Closure';
			} elseif ( is_object( $callback ) ) {
				$callback = get_class( $callback ) . '::__invoke';
			} else {
				$class  = $callback[0];
				$method = $callback[1];
				if ( ! is_string( $class ) ) {
					$class = get_class( $class );
				}
				$callback = "{$class}::$method";
			}
		}

		return $callback;
	}

	/**
	 * Throw an exception if strict mode is on
	 *
	 * @throws \PHPUnit_Framework_ExpectationFailedException
	 *
	 * @param callable $callback
	 */
	protected function strict_check( $callback ) {
		if ( \WP_Mock::strictMode() ) {
			throw new \PHPUnit_Framework_ExpectationFailedException(
				sprintf(
					'Unexpected use of add action for action %s with callback %s',
					$this->name,
					$this->callback_to_string( $callback )
				)
			);
		}
	}

}

class HookedCallbackResponder {

	/**
	 * @var callable
	 */
	protected $callable;

	public function perform( $callable ) {
		$this->callable = $callable;
	}

	public function react() {
		call_user_func( $this->callable );
	}

}
