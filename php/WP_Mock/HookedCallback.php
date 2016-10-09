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
