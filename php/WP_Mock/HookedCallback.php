<?php

namespace WP_Mock;

class HookedCallback extends Hook {

	public function react( $callback, $priority, $argument_count ) {
		\WP_Mock::addHook( $this->name );

		return ( isset( $this->processors[$this->safe_offset( $callback )] ) &&
			isset( $this->processors[$this->safe_offset( $callback )][$priority] ) &&
			isset( $this->processors[$this->safe_offset( $callback )][$priority][$argument_count] )
		) ? $this->processors[$this->safe_offset( $callback )][$priority][$argument_count]->react() : null;
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