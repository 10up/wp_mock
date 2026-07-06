<?php

namespace WP_Mock\Tools;

use Exception;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use Throwable;
use WP_Mock;
use WP_Mock\Tools\Constraints\ExpectationsMet;
use WP_Mock\Tools\Constraints\IsEqualHtml;
use WP_Mock\Traits\AccessInaccessibleClassMembersTrait;
use WP_Mock\Traits\MockWordPressObjectsTrait;

/**
 * WP_Mock test case.
 *
 * Projects using WP_Mock can extend this class in their unit tests.
 */
abstract class TestCase extends PhpUnitTestCase
{
    use AccessInaccessibleClassMembersTrait;
    use MockWordPressObjectsTrait;

    /** @var array<string, Mockery\Mock> */
    protected $mockedStaticMethods = [];

    /** @var array<mixed> */
    protected $__default_post = [];

    /** @var array<mixed> */
    protected $__default_get = [];

    /** @var array<mixed> */
    protected $__default_request = [];

    /** @var array<string> */
    protected $testFiles = [];

    /**
     * Sets up the test case.
     *
     * This method is called before each test.
     *
     * @return void
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->requireFileDependencies();

        WP_Mock::setUp();

        $_GET = (array) $this->__default_get;
        $_POST = (array) $this->__default_post;
        $_REQUEST = (array) $this->__default_request;

        $this->cleanGlobals();
    }

    /**
     * Require any test files that are defined in a subclass.
     *
     * This will only work if the WP_MOCK_INCLUDE_DIR is defined to point to the root directory you want to include files from.
     *
     * @return void
     */
    protected function requireFileDependencies(): void
    {
        if (! empty($this->testFiles) && defined('WP_MOCK_INCLUDE_DIR')) {
            foreach ($this->testFiles as $file) {
                if (file_exists(WP_MOCK_INCLUDE_DIR.$file)) {
                    require_once(WP_MOCK_INCLUDE_DIR.$file);
                }
            }
        }
    }

    /**
     * Tears down the test case.
     *
     * This method is called after each test.
     */
    public function tearDown(): void
    {
        WP_Mock::tearDown();

        $this->cleanGlobals();

        $this->mockedStaticMethods = [];
        $_GET = $_POST = $_REQUEST = [];
    }

    /**
     * Cleans common WordPress globals that may have been used in between tests.
     *
     * @return void
     */
    protected function cleanGlobals(): void
    {
        $commonGlobals = [
            'post',
            'wp_query',
        ];

        foreach ($commonGlobals as $var) {
            if (isset($GLOBALS[$var])) {
                unset($GLOBALS[$var]);
            }
        }
    }

    /**
     * Asserts that all actions have been called.
     *
     * @return void
     * @throws ExpectationFailedException|Exception
     */
    public function assertActionsCalled(): void
    {
        $actionsNotAdded = $expectedActions = 0;

        try {
            WP_Mock::assertActionsCalled();
        } catch (Exception $exception) {
            $actionsNotAdded = 1;
            $expectedActions  = $exception->getMessage();
        }

        $this->assertEmpty($actionsNotAdded, (string) $expectedActions);
    }

    /**
     * Asserts that all hooks have been added.
     *
     * @return void
     * @throws ExpectationFailedException|Exception
     */
    public function assertHooksAdded(): void
    {
        $hooksNotAdded = $expectedHooks = 0;

        try {
            WP_Mock::assertHooksAdded();
        } catch (Exception $exception) {
            $hooksNotAdded = 1;
            $expectedHooks = $exception->getMessage();
        }

        $this->assertEmpty($hooksNotAdded, (string) $expectedHooks);
    }

    /**
     * Asserts that the current test conditions have been met.
     *
     * @deprecated prefer {@see TestCase::assertConditionsMet())
     *
     * @param string $message
     * @return void
     */
    public function assertCurrentConditionsMet(string $message = ''): void
    {
        $this->assertConditionsMet($message);
    }

    /**
     * Asserts that the current test conditions have been met.
     *
     * @param string $message
     * @return void
     */
    public function assertConditionsMet(string $message = ''): void
    {
        /** @phpstan-ignore-next-line it will never throw an exception */
        $this->assertThat(null, new ExpectationsMet(), $message);
    }

    /**
     * Evaluates that an HTML string is equal to another.
     *
     * @param string $expected
     * @param string $actual
     * @param string $message
     * @return void
     * @throws ExpectationFailedException|Exception
     */
    public function assertEqualsHtml(string $expected, string $actual, string $message = ''): void
    {
        $constraint = new IsEqualHtml($expected);

        $this->assertThat($actual, $constraint, $message);
    }

    /**
     * Asserts that the HTML output produced by the given callback equals the expected HTML, ignoring insignificant whitespace.
     *
     * Cross-version replacement for the implicit output filtering that previously relied on
     * PHPUnit's setOutputCallback() (removed in PHPUnit 10) and the expectOutputString() override
     * (expectOutputString() became final in PHPUnit 10).
     *
     * @param string $expectedHtml the expected HTML
     * @param callable $callback code that echoes or prints output
     * @param string $message
     * @return void
     * @throws ExpectationFailedException|Throwable a failed assertion, or any Throwable propagated by $callback
     */
    public function assertOutputEqualsHtml(string $expectedHtml, callable $callback, string $message = ''): void
    {
        ob_start();

        try {
            $callback();
        } finally {
            $actual = ob_get_clean();
        }

        $this->assertThat((string) $actual, new IsEqualHtml($expectedHtml), $message);
    }

    /**
     * Mocks a static method of a class.
     *
     * @param string $class the classname or class::method name
     * @param null|string $method the method name (optional if class::method used for $class)
     * @return Mockery\Expectation
     * @throws InvalidArgumentException|RuntimeException|ReflectionException
     */
    protected function mockStaticMethod(string $class, ?string $method = null)
    {
        if (! $method) {
            [$class, $method] = (explode('::', $class) + [null, null]);
        }

        if (! $method || ! $class) {
            throw new InvalidArgumentException(sprintf('Could not mock %s::%s', $class, $method));
        }

        if (! WP_Mock::usingPatchwork() || ! function_exists('Patchwork\redefine')) {
            throw new RuntimeException('Patchwork is not loaded! Please load patchwork before mocking static methods!');
        }

        $safeMethod = "wp_mock_safe_$method";
        $signature = md5("$class::$method");

        if (! empty($this->mockedStaticMethods[$signature])) {
            $mock = $this->mockedStaticMethods[$signature];
        } else {
            $reflectionMethod = false;

            if (class_exists($class)) {
                $reflectionMethod = new ReflectionMethod($class, $method);
            }

            // throw an exception if method doesn't exist, is not static or has private access
            if ($reflectionMethod && (! $reflectionMethod->isUserDefined() || ! $reflectionMethod->isStatic() || $reflectionMethod->isPrivate())) {
                throw new InvalidArgumentException(sprintf('%s::%s is not a user-defined non-private static method!', $class, $method));
            }

            /** @var Mockery\Mock $mock */
            $mock = Mockery::mock($class);
            $mock->shouldAllowMockingProtectedMethods();
            $this->mockedStaticMethods[$signature] = $mock;

            \Patchwork\redefine("$class::$method", function () use ($mock, $safeMethod) {
                /** @phpstan-ignore-next-line */
                return call_user_func_array([$mock, $safeMethod], func_get_args());
            });
        }

        /** @phpstan-ignore-next-line return Expectation to make PhpStan happy */
        return $mock->shouldReceive($safeMethod);
    }

    /**
     * Returns a function namespaced with the current test class.
     *
     * @deprecated the purpose of this legacy method is not clear and may be removed in a future version of WP_Mock
     *
     * @param mixed $function
     * @return string|mixed
     */
    public function ns($function)
    {
        if (! is_string($function) || false !== strpos($function, '\\')) {
            return $function;
        }

        $thisClassName = trim(get_class($this), '\\');

        if (! strpos($thisClassName, '\\')) {
            return $function;
        }

        // $thisNamespace is constructed by exploding the current class name on
        // namespace separators, running array_slice on that array starting at 0
        // and ending one element from the end (chops the class name off) and
        // imploding that using namespace separators as the glue.
        $thisNamespace = implode('\\', array_slice(explode('\\', $thisClassName), 0, - 1));

        return "$thisNamespace\\$function";
    }
}
