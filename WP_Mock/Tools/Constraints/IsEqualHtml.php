<?php

namespace WP_Mock\Tools\Constraints;

use PHPUnit_Framework_Constraint_IsEqual;

class IsEqualHtml extends PHPUnit_Framework_Constraint_IsEqual {

	/**
	 * @param string $thing
	 *
	 * @return string
	 */
	private function clean( $thing ) {
		$thing = preg_replace( '/\n\s+/', '', $thing );
		$thing = preg_replace( '/\s\s+/', ' ', $thing );

		return str_replace( array( "\r", "\n", "\t" ), '', $thing );
	}

	/**
	 * @param mixed  $other
	 * @param string $description  Optional. Defaults to ''.
	 * @param bool   $returnResult Optional. Defaults to FALSE.
	 *
	 * @return mixed
	 */
	public function evaluate( $other, $description = '', $returnResult = FALSE ) {
		$other       = $this->clean( $other );
		$this->value = $this->clean( $this->value );

		return parent::evaluate( $other, $description, $returnResult );
	}

}
