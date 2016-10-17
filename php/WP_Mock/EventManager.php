<?php

namespace WP_Mock;

class EventManager {
	/**
	 * @var array
	 */
	protected $filters;

	/**
	 * @var array
	 */
	protected $actions;

	/**
	 * @var array
	 */
	protected $expected;

	protected $callbacks;

	public function __construct() {
		$this->flush();
	}

	/**
	 * Clear internal storage.
	 */
	public function flush() {
		$this->filters = array();
		$this->actions = array();
		$this->expected = array();
	}

	/**
	 * @param string $name Action handler to retrieve
	 *
	 * @return Action
	 */
	public function action( $name ) {
		if ( ! isset( $this->actions[ $name ] ) ) {
			$this->actions[ $name ] = new Action( $name );
			$this->expected[] = 'action::' . $name;
		}

		return $this->actions[ $name ];
	}

	/**
	 * @param string $name Filter handler to retrieve
	 *
	 * @return Filter
	 */
	public function filter( $name ) {
		if ( ! isset( $this->filters[ $name ] ) ) {
			$this->filters[ $name ] = new Filter( $name );
			$this->expected[] = 'filter::' . $name;
		}

		return $this->filters[ $name ];
	}

	public function callback( $name, $type = 'filter' ) {
		$type_name = "$type::$name";
		if ( ! isset( $this->callbacks[ $type_name ] ) ) {
			$hookedCallback = new HookedCallback( $name );
			$hookedCallback->setType( $type );
			$this->callbacks[ $type_name ] = $hookedCallback;
			$this->expected[]              = "callback::$type_name";
		}

		return $this->callbacks[ $type_name ];
	}

	/**
	 * Remember that a particular hook has been invoked during operation.
	 *
	 * @param string $hook
	 * @param string $type
	 */
	public function called( $hook, $type = 'action' ) {
		$position = array_search( $type . '::' . $hook, $this->expected );
		array_splice( $this->expected, $position, 1 );
	}

	/**
	 * Return a list of all the actions we're expecting a test to invoke.
	 *
	 * @return array
	 */
	public function expectedActions() {
		return array_keys( $this->actions );
	}

	public function expectedHooks() {
		return array_keys( $this->callbacks );
	}

	/**
	 * Check whether or not all actions have been invoked at least once.
	 *
	 * @return bool
	 */
	public function allActionsCalled() {
		foreach( $this->expected as $hook ) {
			if ( 0 === strpos( $hook, 'action::' ) ) {
				return false;
			}
		}

		return true;
	}

	public function allHooksAdded() {
		foreach( $this->expected as $hook ) {
			if ( 0 === strpos( $hook, 'callback::' ) ) {
				return false;
			}
		}

		return true;
	}
}
