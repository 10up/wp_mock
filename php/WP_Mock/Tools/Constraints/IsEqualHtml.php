<?php

namespace WP_Mock\Tools\Constraints;

use PHPUnit\Framework\Constraint\IsEqual;

class IsEqualHtml extends \PHPUnit\Framework\Constraint\IsEqual {

	private function clean( $thing ) {
		$thing = preg_replace( '/\n\s+/', '', $thing );
		$thing = preg_replace( '/\s\s+/', ' ', $thing );
		return str_replace( array( "\r", "\n", "\t" ), '', $thing );
	}

	public function evaluate( $other, $description = '', $returnResult = FALSE ) {
		$other       = $this->clean( $other );
		$this->value = $this->clean( $this->value );
		return parent::evaluate( $other, $description, $returnResult );
	}

}
