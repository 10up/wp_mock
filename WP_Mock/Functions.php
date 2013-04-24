<?php

namespace WP_Mock;

use Mockery;

class Functions {

	private $mocked_functions = array();

	/**
	 * Constructor for the Functions object
	 */
	public function __construct() {
		Handler::cleanup();
		$this->flush();
	}

	/**
	 * Emptys the mocked_functions array
	 */
	public function flush() {
		Handler::cleanup();
		$this->mocked_functions = array();
	}

	/**
	 * Registers the function to be mocked and sets up its expectations
	 *
	 * @param string $function
	 * @param array  $arguments
	 *
	 * @throws \Exception If the function name is invalid
	 */
	public function register( $function, $arguments ) {
		try {
			$this->generate_function( $function );
			if ( empty( $this->mocked_functions[$function] ) ) {
				$this->mocked_functions[$function] = Mockery::mock( 'wp_api' );
			}
			$mock = $this->mocked_functions[$function];

			$this->set_up_mock( $mock, $function, $arguments );
			Handler::register_handler( $function, $arguments );
		} catch ( \Exception $e ) {
			throw $e;
		}
	}

	/**
	 * Set up the mock object with an expectation for this test.
	 *
	 * @param \Mockery\Mock $mock
	 * @param string        $function
	 * @param array         $arguments
	 */
	protected function set_up_mock( $mock, $function, $arguments ) {
		$expectation = $mock->shouldReceive( $function );

		if ( isset( $arguments['times'] ) ) {
			$times = $arguments['times'];
			if ( is_int( $times ) || preg_match( '/^\d+$/', $times ) ) {
				$expectation->times( $times );
			} elseif ( preg_match( '/^(\d+)([\-+])$/', $times, $matches ) ) {
				$method = '+' === $matches[2] ? 'atLeast' : 'atMost';
				$expectation->$method()->times( $matches[1] );
			} elseif ( preg_match( '/^(\d+)-(\d+)$', $times, $matches ) ) {
				$num1 = (int) $matches[1];
				$num2 = (int) $matches[2];
				if ( $num1 === $num2 ) {
					$expectation->times( $num1 );
				} else {
					$expectation->between( min( $num1, $num2 ), max( $num1, $num2 ) );
				}
			}
		}
		if ( isset( $arguments['args'] ) ) {
			$arguments['args'] = array_map( function ( $argument ) {
				if ( $argument instanceof \Closure ) {
					return Mockery::on( $argument );
				}
				if ( $argument === '*' ) {
					return Mockery::any();
				}
				return $argument;
			}, (array) $arguments['args'] );
			call_user_func_array( array( $expectation, 'with' ), $arguments['args'] );
		}
		if ( isset( $arguments['return_in_order'] ) ) {
			$arguments['return'] = new ReturnSequence();
			$arguments['return']->setReturnValues( $arguments['return_in_order'] );
		}
		if ( isset( $arguments['return'] ) ) {
			$return = $arguments['return'];
			if ( $return instanceof ReturnSequence ) {
				$expectation->andReturnValues( $return->getReturnValues() );
			} elseif ( $return instanceof \Closure ) {
				$expectation->andReturnUsing( $return );
			} else {
				$expectation->andReturn( $return );
			}
		}
	}

	/**
	 * Dynamically declares a function if it doesn't already exist
	 *
	 * This function is namespace-aware.
	 *
	 * @param $function_name
	 *
	 * @throws \Exception If the function name is invalid (either by format or by being a reserved word)
	 */
	private function generate_function( $function_name ) {
		if ( function_exists( $function_name ) ) {
			return;
		}

		$function_name = trim( $function_name, '\\' );
		$parts         = explode( '\\', $function_name );
		$name          = array_pop( $parts );
		$namespace     = empty( $parts ) ? '' : 'namespace ' . implode( '\\', $parts ) . ";\n";

		if ( ! preg_match( '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $function_name ) ) {
			throw new \Exception( 'Function name not properly formatted!' );
		}

		$reserved_words = ' __halt_compiler abstract and array as break callable case catch class clone const continue declare default die do echo else elseif empty enddeclare endfor endforeach endif endswitch endwhile eval exit extends final for foreach function global goto if implements include include_once instanceof insteadof interface isset list namespace new or print private protected public require require_once return static switch throw trait try unset use var while xor __CLASS__ __DIR__ __FILE__ __FUNCTION__ __LINE__ __METHOD__ __NAMESPACE__ __TRAIT__ ';
		if ( false !== strpos( $reserved_words, " $name " ) ) {
			throw new \Exception( 'Function name can not be a reserved word!' );
		}

		$declaration = <<<EOF
$namespace
function $name() {
	return \WP_Mock\Handler::handle_function( '$function_name', func_get_args() );
}
EOF;
		eval( $declaration );
	}

}

