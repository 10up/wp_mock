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

	public function __construct() {
		$this->flush();
	}

	/**
	 * Clear internal storage.
	 */
	public function flush() {
		$this->filters = array();
		$this->actions = array();
	}

	/**
	 * @param string $name Action handler to retrieve
	 *
	 * @return Action
	 */
	public function action( $name ) {
		if ( ! isset( $this->actions[ $name ] ) ) {
			$this->actions[ $name ] = new Action( $name );
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
		}

		return $this->filters[ $name ];
	}
}