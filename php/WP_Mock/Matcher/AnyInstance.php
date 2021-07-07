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
		if( is_string( $expected ) && class_exists( $expected ) ) {
            $reflectedExpected = new \ReflectionClass( $expected );
            $expectedInstance = $reflectedExpected->newInstanceWithoutConstructor();
		} elseif ( is_object( $expected ) ) {
            $expectedInstance = $expected;
		} else {
			throw new Exception( 'AnyInstance matcher can only match objects!' );
		}

		parent::__construct($expectedInstance);
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

		if( $actual instanceof \Closure ) {
		    return false;
        }

        if( get_class( $actual ) === get_class( $this->_expected ) ) {
            return true;
        }

        // parent::haveCommonAncestor() expects two objects.
        $reflectedExpected = new \ReflectionClass( $this->_expected );
        $expectedInstance = $reflectedExpected->newInstanceWithoutConstructor();
		if ( ! $this->haveCommonAncestor( $actual, $expectedInstance ) ) {
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
	    $classname = get_class($this->_expected);
		return "<AnyInstance[{$classname}]>";
	}

}
