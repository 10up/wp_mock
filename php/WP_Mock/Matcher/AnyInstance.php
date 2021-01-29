<?php
// Match on class type.

namespace WP_Mock\Matcher;

use Mockery\Exception;
use Mockery\Matcher\MatcherAbstract;

class AnyInstance extends FuzzyObject {

	/**
	 * @param string|object $expected A classname or instance of a class whose type should match.
	 *
	 * @throws \Mockery\Exception
	 */
	public function __construct( $expected = null ) {
		if( is_string( $expected ) && class_exists( $expected )) {
			$classname = $expected;
		}elseif ( is_object( $expected ) ) {
			$classname = get_class( $expected );
		} else {
			throw new Exception( 'AnyInstance
			 matcher can only match objects!' );
		}

		$this->_expected = $classname;

	}

	/**
	 * Check if the actual value matches the expected.
	 * Actual passed by reference to preserve reference trail (where applicable)
	 * back to the original method parameter.
	 *
	 * @param mixed $actual
	 *
	 * @return bool
	 */
	public function match( &$actual ) {
		if ( ! is_object( $actual ) ) {
			return false;
		}

		$classname = get_class( $actual );

		if ( ! $this->haveCommonAncestor( $actual, $this->_expected ) ) {
			return false;
		}

		$expected_properties = get_object_vars( $this->_expected );

		foreach ( $expected_properties as $prop => $value ) {
			if ( ! isset( $actual->$prop ) || $value !== $actual->$prop ) {
				return false;
			}
		}

		$actual_keys  = array_keys( get_object_vars( $actual ) );
		$extra_actual = array_diff( $actual_keys, array_keys( $expected_properties ) );
		if ( ! empty( $extra_actual ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Return a string representation of this Matcher
	 *
	 * @return string
	 */
	public function __toString() {
		return "<AnyInstance[{$this->_expected}]>";
	}

}
