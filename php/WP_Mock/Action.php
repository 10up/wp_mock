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
		\WP_Mock::invokeAction( $this->name );

		$arg_num = count( $args );

		if ( 0 === $arg_num ) {
			if ( ! isset( $this->processors['argsnull'] ) ) {
				$this->strict_check();

				return;
			}

			$this->processors['argsnull']->react();
		} else {
			$processors = $this->processors;
			for ( $i = 0; $i < $arg_num - 1; $i ++ ) {
				$arg = $this->safe_offset( $args[ $i ] );

				if ( ! isset( $processors[ $arg ] ) ) {
					$this->strict_check();

					return;
				}

				$processors = $processors[ $arg ];
			}

			$arg = $this->safe_offset( $args[ $arg_num - 1 ] );
			if ( ! is_array( $processors ) || ! isset( $processors[ $arg ] ) ) {
				$this->strict_check();

				return;
			}

			$processors[ $arg ]->react();
		}
	}

	protected function new_responder() {
		return new Action_Responder();
	}

	/**
	 * @return string
	 */
	protected function get_strict_mode_message() {
		return sprintf( 'Unexpected use of do_action for action %s', $this->name );
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
