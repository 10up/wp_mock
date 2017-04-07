<?php

namespace WP_Mock\Tools\Constraints;

use PHPUnit_Framework_Constraint;
use Mockery;
use Exception;

class ExpectationsMet extends PHPUnit_Framework_Constraint {

	private $_mockery_message;

	public function matches() {
		try {
			Mockery::getContainer()->mockery_verify();
		} catch ( Exception $e ) {
			$this->_mockery_message = $e->getMessage();
			return false;
		}
		return true;
	}

	/**
	 * Returns a string representation of the object.
	 *
	 * @return string
	 */
	public function toString() {
		return 'WP Mock expectations are met';
	}

	protected function additionalFailureDescription() {
		return str_replace( array( "\r", "\n" ), '', (string) $this->_mockery_message );
	}

	protected function failureDescription() {
		return $this->toString();
	}

}
