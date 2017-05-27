<?php

namespace WP_Mock;

use Mockery;

class Functions {

	private $mocked_functions = array();

	private $internal_functions = array();

	private static $wp_mocked_functions = array();

	private $patchwork_functions = array();

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
		$this->mocked_functions = array();
		Handler::cleanup();
		$this->patchwork_functions = array();
		if ( function_exists( 'Patchwork\undoAll' ) ) {
			\Patchwork\restoreAll();
		}
		if ( empty( self::$wp_mocked_functions ) ) {
			self::$wp_mocked_functions = array(
				'add_action',
				'do_action',
				'add_filter',
				'apply_filters',
				'esc_attr',
				'esc_html',
				'esc_js',
				'esc_textarea',
				'esc_url',
				'esc_url_raw',
				'__',
				'_e',
				'_x',
				'esc_attr__',
				'esc_attr_e',
				'esc_attr_x',
				'esc_html__',
				'esc_html_e',
				'esc_html_x',
				'_n',
			);
		}
	}

	/**
	 * Registers the function to be mocked and sets up its expectations
	 *
	 * @param string $function
	 * @param array  $arguments
	 *
	 * @throws \Exception If the function name is invalid
	 *
	 * @return Mockery\Expectation
	 */
	public function register( $function, $arguments ) {
		$expectation = null;
		try {
			$this->generate_function( $function );
			if ( empty( $this->mocked_functions[$function] ) ) {
				$this->mocked_functions[$function] = Mockery::mock( 'wp_api' );
			}
			$mock = $this->mocked_functions[$function];

			$method = preg_replace( '/\\\\+/', '_', $function );
			$expectation = $this->set_up_mock( $mock, $method, $arguments );
			Handler::register_handler( $function, array( $mock, $method ) );
		} catch ( \Exception $e ) {
			throw $e;
		}
		return $expectation;
	}

	/**
	 * Sets up an argument placeholder that allows it to be any of an enumerated
	 * list of possibilities
	 *
	 * @return \Mockery\Matcher\anyOf
	 */
	public static function anyOf() {
		return call_user_func_array( array( '\\Mockery', 'anyOf' ), func_get_args() );
	}

	/**
	 * Sets up an argument placeholder that requires the argument to be of a
	 * certain type
	 *
	 * This may be any type for which there is a "is_*" function, or any class or
	 * interface.
	 *
	 * @param string $expected
	 *
	 * @return Mockery\Matcher\Type
	 */
	public static function type( $expected ) {
		return Mockery::type( $expected );
	}

	/**
	 * Set up the mock object with an expectation for this test.
	 *
	 * @param \Mockery\Mock $mock
	 * @param string        $function
	 * @param array         $arguments
	 *
	 * @return Mockery\Expectation
	 */
	protected function set_up_mock( $mock, $function, $arguments ) {
		$expectation = $mock->shouldReceive( $function );

		if ( isset( $arguments['times'] ) ) {
			$times = $arguments['times'];
			if ( is_int( $times ) || preg_match( '/^\d+$/', $times ) ) {
				$expectation->times( (int) $times );
			} elseif ( preg_match( '/^(\d+)([\-+])$/', $times, $matches ) ) {
				$method = '+' === $matches[2] ? 'atLeast' : 'atMost';
				$expectation->$method()->times( (int) $matches[1] );
			} elseif ( preg_match( '/^(\d+)-(\d+)$/', $times, $matches ) ) {
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
		if ( isset( $arguments['return_arg'] ) ) {
			$argument_position   = true === $arguments['return_arg'] ? 0 : (int) $arguments['return_arg'];
			$arguments['return'] = function () use ( $argument_position ) {
				if ( $argument_position >= func_num_args() ) {
					return null;
				}
				return func_get_arg( $argument_position );
			};
		} elseif ( isset( $arguments['return_in_order'] ) ) {
			$arguments['return'] = new ReturnSequence();
			$arguments['return']->setReturnValues( (array) $arguments['return_in_order'] );
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
		return $expectation;
	}

	/**
	 * Dynamically declares a function if it doesn't already exist
	 *
	 * This function is namespace-aware.
	 *
	 * @param $function_name
	 */
	private function generate_function( $function_name ) {
		$function_name = $this->sanitize_function_name( $function_name );

		$this->validate_function_name( $function_name );

		$this->create_function( $function_name ) OR $this->replace_function( $function_name );
	}

	/**
	 * Create a function with WP_Mock
	 *
	 * @param string $function_name
	 *
	 * @return bool True if this function created the mock, false otherwise
	 */
	private function create_function( $function_name ) {
		if ( in_array( $function_name, self::$wp_mocked_functions ) ) {
			return true;
		}
		if ( function_exists( $function_name ) ) {
			return false;
		}

		$parts     = explode( '\\', $function_name );
		$name      = array_pop( $parts );
		$namespace = empty( $parts ) ? '' : 'namespace ' . implode( '\\', $parts ) . ';' . PHP_EOL;

		$declaration = <<<EOF
$namespace
function $name() {
	return \\WP_Mock\\Handler::handle_function( '$function_name', func_get_args() );
}
EOF;
		eval( $declaration );

		self::$wp_mocked_functions[] = $function_name;

		return true;
	}

	/**
	 * Replace a function with patchwork
	 *
	 * @param string $function_name
	 *
	 * @return bool
	 */
	private function replace_function( $function_name ) {
		if ( in_array( $function_name, $this->patchwork_functions ) ) {
			return true;
		}
		if ( ! function_exists( 'Patchwork\\replace' ) ) {
			return true;
		}
		$this->patchwork_functions[] = $function_name;
		\Patchwork\redefine( $function_name, function () use ( $function_name ) {
			return Handler::handle_function( $function_name, func_get_args() );
		} );
		return true;
	}

	/**
	 * Clean the function name to be of a standard shape
	 *
	 * @param string $function_name
	 *
	 * @return string
	 */
	private function sanitize_function_name( $function_name ) {
		$function_name = trim( $function_name, '\\' );
		return $function_name;
	}

	/**
	 * Validate the function name for format and other considerations
	 *
	 * Validation will fail if the string doesn't match the regex, if it's an
	 * internal function, or if it is a reserved word in PHP.
	 *
	 * @param string $function_name
	 *
	 * @throws \InvalidArgumentException
	 */
	private function validate_function_name( $function_name ) {
		if ( function_exists( $function_name ) ) {
			if ( empty( $this->internal_functions ) ) {
				$defined_functions        = get_defined_functions();
				$this->internal_functions = $defined_functions['internal'];
			}
			if ( in_array( $function_name, $this->internal_functions ) ) {
				throw new \InvalidArgumentException;
			}
		}

		$parts = explode( '\\', $function_name );
		$name  = array_pop( $parts );

		if ( ! preg_match( '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $function_name ) ) {
			throw new \InvalidArgumentException( 'Function name not properly formatted!' );
		}

		$reserved_words = ' __halt_compiler abstract and array as break callable case catch class clone const continue declare default die do echo else elseif empty enddeclare endfor endforeach endif endswitch endwhile eval exit extends final for foreach function global goto if implements include include_once instanceof insteadof interface isset list namespace new or print private protected public require require_once return static switch throw trait try unset use var while xor __CLASS__ __DIR__ __FILE__ __FUNCTION__ __LINE__ __METHOD__ __NAMESPACE__ __TRAIT__ ';
		if ( false !== strpos( $reserved_words, " $name " ) ) {
			throw new \InvalidArgumentException( 'Function name can not be a reserved word!' );
		}
	}

}

