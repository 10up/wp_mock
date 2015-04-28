<?php

namespace WP_Mock;

class ReturnSequence {

	private $return_values = array();

	/**
	 * Constructor to set up the return sequence object
	 *
	 * You can pass arbitrary arguments to the constructor to set to the internal
	 * $return_values array
	 */
	public function __construct() {
		$this->return_values = func_get_args();
	}

	/**
	 * Retrieve the $return_values array
	 *
	 * @return array
	 */
	public function getReturnValues() {
		return $this->return_values;
	}

	/**
	 * Set the return_values array
	 *
	 * Values should be passed in as one array. Keys will be discarded.
	 *
	 * @param array $return_values
	 */
	public function setReturnValues( $return_values ) {
		$this->return_values = array_values( (array) $return_values );
	}

}