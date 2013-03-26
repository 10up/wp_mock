<?php
/**
 * Mock WordPress actions by substituting each action with an advanced object
 * capable of intercepting calls and returning predictable behavior.
 *
 * @package WP_Mock
 * @subpackage Hooks
 */

namespace WP_Mock;


class Action extends Hook {
	public function react( $args ) {
		$arg_num = count( $args );

		$processors = $this->processors;
		for( $i = 0; $i < $arg_num - 1; $i++ ) {
			$arg = $args[ $i ];

			if ( ! isset( $processors[ $arg ] ) ) {
				return;
			}

			$processors = $processors[ $arg ];
		}

		$processors[ $args[ $arg_num - 1 ] ]->react();
	}

	protected function new_responder() {
		return new Action_Responder();
	}
}

class Action_Responder {
	/**
	 * @var mixed
	 */
	protected $callable;

	public function perform( $callable ) {
		$this->callable = $callable;
	}

	public function react() {
		call_user_func( $this->callable );
	}
}