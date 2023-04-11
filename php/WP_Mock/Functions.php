<?php

namespace WP_Mock;

use Closure;
use InvalidArgumentException;
use Mockery;
use Mockery\Matcher\AnyOf;
use Mockery\Matcher\Type;
use WP_Mock;

/**
 * Functions mocking handler.
 *
 * This internal class is responsible for mocking WordPress functions and methods.
 *
 * @see WP_Mock::userFunction()
 * @see WP_Mock::echoFunction()
 * @see WP_Mock::passthruFunction()
 */
class Functions
{
    /** @var array<string, Mockery\Mock> functions mocked by WP_Mock using Mockery */
    private $mockedFunctions = [];

    /** @var string[] list of internal functions */
    private $internalFunctions = [];

    /** @var string[] list of WordPress functions */
    private static $wpMockedFunctions = [];

    /** @var string[] list of Patchwork functions */
    private $patchworkFunctions = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        Handler::cleanup();

        $this->flush();
    }

    /**
     * Flushes (resets) the registered mocked functions.
     *
     * @return void
     */
    public function flush(): void
    {
        $this->mockedFunctions = [];

        Handler::cleanup();

        $this->patchworkFunctions = [];

        if (function_exists('Patchwork\undoAll')) {
            \Patchwork\restoreAll();
        }

        if (empty(self::$wpMockedFunctions)) {
            self::$wpMockedFunctions = [
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
            ];
        }
    }

    /**
     * Registers a function to be mocked and sets up its expectations.
     *
     * @param string $function function name
     * @param array<string, mixed> $args optional arguments
     * @return Mockery\ExpectationInterface|Mockery\Expectation
     * @throws InvalidArgumentException
     */
    public function register(string $function, array $args = []): Mockery\ExpectationInterface
    {
        $this->generateFunction($function);

        if (empty($this->mockedFunctions[$function])) {
            /** @phpstan-ignore-next-line */
            $this->mockedFunctions[$function] = Mockery::mock('wp_api');
        }

        /** @var Mockery\Mock $mock */
        $mock = $this->mockedFunctions[$function];

        /** @var string $method */
        $method = preg_replace('/\\\\+/', '_', $function);

        $expectation = $this->setUpMock($mock, $method, $args);

        Handler::register_handler($function, [$mock, $method]);

        return $expectation;
    }

    /**
     * Sets up an argument placeholder that allows it to be any of an enumerated list of possibilities.
     *
     * @return AnyOf
     */
    public static function anyOf(): AnyOf
    {
        /** @phpstan-ignore-next-line */
        return call_user_func_array(['\\Mockery', 'anyOf'], func_get_args());
    }

    /**
     * Sets up an argument placeholder that requires the argument to be of a certain type.
     *
     * This may be any type for which there is a "is_*" function, or any class or interface.
     *
     * @param string $expected
     * @return Type
     */
    public static function type(string $expected): Type
    {
        return Mockery::type($expected);
    }

    /**
     * Sets up the mock object with an expectation for this test.
     *
     * @param Mockery\Mock|Mockery\MockInterface|Mockery\LegacyMockInterface $mock mock object
     * @param string $function function name
     * @param array<string, mixed> $args optional arguments for expectations
     * @return Mockery\ExpectationInterface|Mockery\Expectation
     */
    protected function setUpMock($mock, string $function, array $args = []): Mockery\ExpectationInterface
    {
        /** @var Mockery\Expectation $expectation */
        $expectation = $mock->shouldReceive($function);

        if (isset($args['times'])) {

            $times = $args['times'];

            if (is_int($times) || (is_string($times) && preg_match('/^\d+$/', $times))) {
                /** @phpstan-ignore-next-line the argument passed is valid */
                $expectation->times((int) $times);
            } elseif (is_string($times)) {
                if (preg_match('/^(\d+)([\-+])$/', $times, $matches)) {
                    $method = '+' === $matches[2] ? 'atLeast' : 'atMost';

                    $expectation->$method()->times((int) $matches[1]);
                } elseif (preg_match('/^(\d+)-(\d+)$/', $times, $matches)) {
                    $num1 = (int) $matches[1];
                    $num2 = (int) $matches[2];

                    if ($num1 === $num2) {
                        /** @phpstan-ignore-next-line the argument passed is valid */
                        $expectation->times($num1);
                    } else {
                        $expectation->between(min($num1, $num2), max($num1, $num2));
                    }
                }
            }
        }

        if (isset($args['args'])) {
            $args['args'] = array_map(function ($argument) {
                if ($argument instanceof Closure) {
                    return Mockery::on($argument);
                }

                if ($argument === '*') {
                    return Mockery::any();
                }

                return $argument;
            }, (array) $args['args']);

            /** @phpstan-ignore-next-line */
            call_user_func_array([$expectation, 'with'], $args['args']);
        }

        if (isset($args['return_arg'])) {
            /** @phpstan-ignore-next-line */
            $argPosition = true === $args['return_arg'] ? 0 : (int) $args['return_arg'];

            $args['return'] = function () use ($argPosition) {
                if ($argPosition >= func_num_args()) {
                    return null;
                }

                /** @phpstan-ignore-next-line */
                return func_get_arg($argPosition);
            };
        } elseif (isset($args['return_in_order'])) {
            $args['return'] = new ReturnSequence();
            $args['return']->setReturnValues((array) $args['return_in_order']);
        }

        if (isset($args['return'])) {
            $return = $args['return'];

            if ($return instanceof ReturnSequence) {
                $expectation->andReturnValues($return->getReturnValues());
            } elseif ($return instanceof Closure) {
                $expectation->andReturnUsing($return);
            } else {
                $expectation->andReturn($return);
            }
        }

        return $expectation;
    }

    /**
     * Dynamically declares a function if it doesn't already exist.
     *
     * The declared function is namespace-aware.
     *
     * @param string $functionName function name
     * @return void
     * @throws InvalidArgumentException
     */
    private function generateFunction(string $functionName): void
    {
        $functionName = $this->sanitizeFunctionName($functionName);

        $this->validateFunctionName($functionName);

        $this->createFunction($functionName) or $this->replaceFunction($functionName);
    }

    /**
     * Creates a function using eval.
     *
     * @param string $functionName function name
     * @return bool true if this function created the mock, false otherwise
     */
    private function createFunction(string $functionName): bool
    {
        if (in_array($functionName, self::$wpMockedFunctions, true)) {
            return true;
        }

        if (function_exists($functionName)) {
            return false;
        }

        $parts = explode('\\', $functionName);
        $name = array_pop($parts);
        $namespace = empty($parts) ? '' : 'namespace '.implode('\\', $parts).';'.PHP_EOL;

        $declaration = <<<EOF
$namespace
function $name() {
	return \\WP_Mock\\Handler::handle_function('$functionName', func_get_args());
}
EOF;
        eval($declaration);

        self::$wpMockedFunctions[] = $functionName;

        return true;
    }

    /**
     * Replaces a function using Patchwork.
     *
     * @param string $functionName function name
     * @return bool
     */
    private function replaceFunction(string $functionName): bool
    {
        if (in_array($functionName, $this->patchworkFunctions, true)) {
            return true;
        }

        if (! function_exists('Patchwork\\replace')) {
            return true;
        }

        $this->patchworkFunctions[] = $functionName;

        \Patchwork\redefine($functionName, function () use ($functionName) {
            return Handler::handle_function($functionName, func_get_args());
        });

        return true;
    }

    /**
     * Cleans a function name to be of a standard shape.
     *
     * Trims any namespace separators from the beginning and end of the function name.
     *
     * @param string $functionName
     * @return string
     */
    private function sanitizeFunctionName(string $functionName): string
    {
        return trim($functionName, '\\');
    }

    /**
     * Validates a function name for format and other considerations.
     *
     * Validation will fail if not a valid function name, if it's an internal function, or if it is a reserved word in PHP.
     *
     * @param string $functionName
     * @return void
     * @throws InvalidArgumentException
     */
    private function validateFunctionName(string $functionName): void
    {
        if (function_exists($functionName)) {
            if (empty($this->internalFunctions)) {
                $definedFunctions = get_defined_functions();
                $this->internalFunctions = $definedFunctions['internal'];
            }

            if (in_array($functionName, $this->internalFunctions)) {
                throw new InvalidArgumentException('Cannot override internal PHP functions!');
            }
        }

        $parts = explode('\\', $functionName);
        $name = array_pop($parts);

        if (! preg_match('/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $functionName)) {
            throw new InvalidArgumentException('Function name not properly formatted!');
        }

        $reservedWords = ' __halt_compiler abstract and array as break callable case catch class clone const continue declare default die do echo else elseif empty enddeclare endfor endforeach endif endswitch endwhile eval exit extends final for foreach function global goto if implements include include_once instanceof insteadof interface isset list namespace new or print private protected public require require_once return static switch throw trait try unset use var while xor __CLASS__ __DIR__ __FILE__ __FUNCTION__ __LINE__ __METHOD__ __NAMESPACE__ __TRAIT__ ';

        if (false !== strpos($reservedWords, " $name ")) {
            throw new InvalidArgumentException('Function name can not be a reserved word!');
        }
    }
}
