<?php

namespace WP_Mock;

use Closure;
use InvalidArgumentException;
use Mockery;
use Mockery\Matcher\AnyOf;
use Mockery\Matcher\Type;
use WP_Mock;
use WP_Mock\Functions\Handler;
use WP_Mock\Functions\ReturnSequence;

/**
 * Functions mocking manager.
 *
 * This internal class is responsible for mocking WordPress functions and methods.
 *
 * @see WP_Mock::userFunction()
 * @see WP_Mock::echoFunction()
 * @see WP_Mock::passthruFunction()
 */
class Functions
{
    /** @var array<string, Mockery\Mock> container of function names holding a Mock object each handled by WP_Mock */
    private array $mockedFunctions = [];

    /** @var string[] list of user-defined functions (e.g. WordPress functions) mocked by WP_Mock */
    private static array $userMockedFunctions = [];

    /** @var string[] list of functions redefined by WP_Mock through Patchwork */
    private array $patchworkFunctions = [];

    /** @var string[] list of PHP internal functions as per {@see get_defined_functions()} */
    private array $internalFunctions = [];

    /**
     * Initializes the handler.
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

        if (empty(self::$userMockedFunctions)) {
            self::$userMockedFunctions = [
                '__',
                '_e',
                '_n',
                '_x',
                'add_action',
                'add_filter',
                'apply_filters',
                'do_action',
                'esc_attr',
                'esc_attr__',
                'esc_attr_e',
                'esc_attr_x',
                'esc_html',
                'esc_html__',
                'esc_html_e',
                'esc_html_x',
                'esc_js',
                'esc_textarea',
                'esc_url',
                'esc_url_raw',
            ];
        }
    }

    /**
     * Registers a function to be mocked and sets up its expectations.
     *
     * @param string|callable-string $function function name
     * @param array<string, mixed> $args optional arguments
     * @return Mockery\Expectation
     * @throws InvalidArgumentException
     */
    public function register(string $function, array $args = [])
    {
        $this->generateFunction($function);

        if (empty($this->mockedFunctions[$function])) {
            /** @phpstan-ignore-next-line */
            $this->mockedFunctions[$function] = Mockery::mock('wp_api');
        }

        /** @var Mockery\Mock $mock */
        $mock = $this->mockedFunctions[$function];

        /** @var callable-string $method */
        $method = preg_replace('/\\\\+/', '_', $function);

        /** @var Mockery\Expectation $expectation */
        $expectation = $this->setUpMock($mock, $method, $args);

        Handler::registerHandler($function, [$mock, $method]);

        return $expectation;
    }

    /**
     * Sets up the mock object with expectations.
     *
     * @param Mockery\Mock|Mockery\MockInterface|Mockery\LegacyMockInterface $mock mock object
     * @param string $functionName function name
     * @param array<string, mixed> $args optional arguments for setting expectations on the mock
     * @return Mockery\Expectation|Mockery\CompositeExpectation
     */
    protected function setUpMock($mock, string $functionName, array $args = [])
    {
        /** @var Mockery\Expectation|Mockery\CompositeExpectation $expectation */
        $expectation = $mock->shouldReceive($functionName);

        // set the expected times the function should be called
        if (isset($args['times'])) {
            $this->setExpectedTimes($expectation, $args['times']);
        }

        // set the expected arguments the function should be called with
        if (isset($args['args'])) {
            $this->setExpectedArgs($expectation, $args['args']);
        }

        // set the expected return value based on a passed argument or return values for each call in order
        if (isset($args['return_arg']) || isset($args['return_in_order'])) {
            $args['return'] = $this->parseExpectedReturn($args);
        }

        // set the expected return value of the function
        if (isset($args['return'])) {
            $this->setExpectedReturn($expectation, $args['return']);
        }

        return $expectation;
    }

    /**
     * Sets the expected times a function should be called based on arguments.
     *
     * @param Mockery\Expectation|Mockery\CompositeExpectation $expectation
     * @param int|string|mixed $times
     * @return Mockery\Expectation|Mockery\CompositeExpectation
     */
    protected function setExpectedTimes(&$expectation, $times)
    {
        if (is_int($times) || (is_string($times) && preg_match('/^\d+$/', $times))) {
            /** @phpstan-ignore-next-line method exists */
            $expectation->times((int) $times);
        } elseif (is_string($times)) {
            if (preg_match('/^(\d+)([\-+])$/', $times, $matches)) {
                $method = '+' === $matches[2] ? 'atLeast' : 'atMost';

                $expectation->$method()->times((int) $matches[1]);
            } elseif (preg_match('/^(\d+)-(\d+)$/', $times, $matches)) {
                $num1 = (int) $matches[1];
                $num2 = (int) $matches[2];

                if ($num1 === $num2) {
                    /** @phpstan-ignore-next-line method exists */
                    $expectation->times($num1);
                } else {
                    /** @phpstan-ignore-next-line method exists */
                    $expectation->between(min($num1, $num2), max($num1, $num2));
                }
            }
        }

        return $expectation;
    }

    /**
     * Sets the expected arguments that a function should be called with.
     *
     * @param Mockery\Expectation|Mockery\CompositeExpectation $expectation
     * @param mixed $args expected arguments passed to the function
     * @return Mockery\Expectation|Mockery\CompositeExpectation
     */
    protected function setExpectedArgs(&$expectation, $args)
    {
        $args = array_map(function ($argument) {
            if ($argument instanceof Closure) {
                return Mockery::on($argument);
            }

            if ($argument === '*') {
                return Mockery::any();
            }

            return $argument;
        }, (array) $args);

        /** @phpstan-ignore-next-line method exists on expectation */
        call_user_func_array([$expectation, 'with'], $args);

        return $expectation;
    }

    /**
     * Parses arguments for setting the expectation `return` arg.
     *
     * @param array<string, mixed> $args
     * @return Closure|ReturnSequence|null
     */
    protected function parseExpectedReturn(array $args)
    {
        $returnValue = null;

        if (isset($args['return_arg'])) {
            /** @phpstan-ignore-next-line */
            $argPosition = max(true === $args['return_arg'] ? 0 : (int) $args['return_arg'], 0);

            // set the expected return value based on an argument passed to the function
            $returnValue = function () use ($argPosition) {
                if ($argPosition >= func_num_args()) {
                    return null;
                }

                return func_get_arg($argPosition);
            };
        } elseif (isset($args['return_in_order'])) {
            // sets the return values for each call in order
            $returnValue = new ReturnSequence();
            $returnValue->setReturnValues((array) $args['return_in_order']);
        }

        return $returnValue;
    }

    /**
     * Sets the expected return value for the expectation.
     *
     * @param Mockery\Expectation $expectation
     * @param Closure|ReturnSequence|mixed $return
     * @return Mockery\Expectation
     */
    protected function setExpectedReturn(&$expectation, $return)
    {
        if ($return instanceof ReturnSequence) {
            $expectation->andReturnValues($return->getReturnValues());
        } elseif ($return instanceof Closure) {
            $expectation->andReturnUsing($return);
        } else {
            $expectation->andReturn($return);
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
    protected function generateFunction(string $functionName): void
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
    protected function createFunction(string $functionName): bool
    {
        if (in_array($functionName, self::$userMockedFunctions, true)) {
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
	return \\WP_Mock\\Functions\\Handler::handleFunction('$functionName', func_get_args());
}
EOF;
        eval($declaration);

        self::$userMockedFunctions[] = $functionName;

        return true;
    }

    /**
     * Replaces a function using Patchwork.
     *
     * @param string $functionName function name
     * @return bool
     */
    protected function replaceFunction(string $functionName): bool
    {
        if (in_array($functionName, $this->patchworkFunctions, true)) {
            return true;
        }

        if (! function_exists('Patchwork\\replace')) {
            return true;
        }

        $this->patchworkFunctions[] = $functionName;

        \Patchwork\redefine($functionName, function () use ($functionName) {
            return Handler::handleFunction($functionName, func_get_args());
        });

        return true;
    }

    /**
     * Cleans a function name to be of a standard shape.
     *
     * Trims any namespace separators from the function name.
     *
     * @param string $functionName
     * @return string
     */
    protected function sanitizeFunctionName(string $functionName): string
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
    protected function validateFunctionName(string $functionName): void
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
            throw new InvalidArgumentException('Function name cannot be a reserved word!');
        }
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
}
