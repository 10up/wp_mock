<?php

namespace WP_Mock\Matcher;

use Mockery\Exception;
use Mockery\Matcher\MatcherAbstract;

class FuzzyObject extends MatcherAbstract {

	/**
	 * @param object|array $expected
	 *
	 * @throws \Mockery\Exception If a non-object non-array expectation is provided
	 */
	public function __construct( $expected = null ) {
		if ( ! is_object( $expected ) ) {
			if ( is_array( $expected ) ) {
				$expected = (object) $expected;
			} else {
				throw new Exception( 'FuzzyObject matcher can only match objects!' );
			}
		}
		parent::__construct( $expected );
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
		$values = array_values( get_object_vars( $this->_expected ) );
		$values = array_map( function ( $value ) {
			if ( ! is_scalar( $value ) ) {
				if ( is_array( $value ) ) {
					$value = 'Array';
				} elseif ( is_object( $value ) ) {
					$value = get_class( $value );
				} elseif ( is_resource( $value ) ) {
					$value = get_resource_type( $value );
				} else {
					$value = 'unknown';
				}
			}
			return $value;
		}, $values );
		return '<FuzzyObject[' . implode( ', ', $values ) . ']>';
	}

	/**
	 * @param object $object1
	 * @param object $object2
	 *
	 * @return bool
	 */
	protected function haveCommonAncestor( $object1, $object2 ) {
		$class1 = get_class( $object1 );
		$class2 = get_class( $object2 );
		if ( $class1 === $class2 ) {
			return true;
		}
		$inheritance1 = class_parents( $class1 );
		$inheritance2 = class_parents( $class2 );
		return (bool) array_intersect_assoc( $inheritance1, $inheritance2 );
	}
}
