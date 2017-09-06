<?php

namespace WP_Mock;

class InvokedFilterValue {

	/**
	 * @var callable
	 */
	protected $callback;

	/**
	 * InvokedFilterValue constructor.
	 *
	 * @param callable $callable
	 */
	public function __construct( $callable ) {
		$this->callback = $callable;
	}

	public function __invoke() {
		return call_user_func_array( $this->callback, func_get_args() );
	}

}
