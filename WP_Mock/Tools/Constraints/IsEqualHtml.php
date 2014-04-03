<?php

namespace WP_Mock\Tools\Constraints;

use PHPUnit_Framework_Constraint_IsEqual;

class IsEqualHtml extends PHPUnit_Framework_Constraint_IsEqual {

	private function clean( $thing ) {
		return str_replace( [ "\r", "\n", "\t" ], '', $thing );
	}

	public function evaluate( $other, $description = '', $returnResult = FALSE ) {
		$other       = $this->clean( $other );
		$this->value = $this->clean( $this->value );
		return parent::evaluate( $other, $description, $returnResult );
	}

}
