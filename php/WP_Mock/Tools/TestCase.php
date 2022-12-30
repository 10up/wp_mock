<?php

namespace WP_Mock\Tools;

use PHPUnit\Framework\TestResult;
use Exception;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Util\Test as TestUtil;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;
use WP_Mock;
use WP_Mock\Tools\Constraints\ExpectationsMet;
use WP_Mock\Tools\Constraints\IsEqualHtml;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected $mockedStaticMethods = array();

    /**
     * @var array
     */
    protected $__default_post = array();

    /**
     * @var array
     */
    protected $__default_get = array();

    /**
     * @var array
     */
    protected $__default_request = array();

    /**
     * @var bool|callable
     */
    protected $__contentFilterCallback = false;

    /**
     * @var array
     */
    protected $testFiles = array();

    public function setUp(): void
    {
        $this->requireFileDependencies();

        WP_Mock::setUp();

        $_GET     = (array) $this->__default_get;
        $_POST    = (array) $this->__default_post;
        $_REQUEST = (array) $this->__default_request;

        $this->setUpContentFiltering();

        $this->cleanGlobals();
    }

    public function tearDown(): void
    {
        WP_Mock::tearDown();

        $this->cleanGlobals();

        $this->mockedStaticMethods = array();

        $_GET     = array();
        $_POST    = array();
        $_REQUEST = array();
    }

    public function assertActionsCalled()
    {
        $actions_not_added = $expected_actions = 0;
        try {
            WP_Mock::assertActionsCalled();
        } catch (Exception $e) {
            $actions_not_added = 1;
            $expected_actions  = $e->getMessage();
        }
        $this->assertEmpty($actions_not_added, $expected_actions);
    }

    public function assertHooksAdded()
    {
        $hooks_not_added = $expected_hooks = 0;
        try {
            WP_Mock::assertHooksAdded();
        } catch (Exception $e) {
            $hooks_not_added = 1;
            $expected_hooks  = $e->getMessage();
        }
        $this->assertEmpty($hooks_not_added, $expected_hooks);
    }

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

    public function stripTabsAndNewlines($content)
    {
        return str_replace(array( "\t", "\r", "\n" ), '', $content);
    }

    public function expectOutputString(string $expectedString): void
    {
        if (is_callable($this->__contentFilterCallback)) {
            $expectedString = call_user_func($this->__contentFilterCallback, $expectedString);
        }
        parent::expectOutputString($expectedString);
    }

    public function assertCurrentConditionsMet($message = '')
    {
        $this->assertThat(null, new ExpectationsMet(), $message);
    }

    public function assertConditionsMet($message = '')
    {
        $this->assertCurrentConditionsMet($message);
    }

    public function assertEqualsHTML($expected, $actual, $message = '')
    {
        $constraint = new IsEqualHtml($expected);
        $this->assertThat($actual, $constraint, $message);
    }


    /**
     * Mocks a static method of a class.
     *
     * @param string $class the classname or class::method name
     * @param null|string $method the method name (optional if class::method used for $class)
     * @return Mockery\ExpectationInterface|Mockery\Expectation|Mockery\HigherOrderMessage
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

        $safe_method = "wp_mock_safe_$method";
        $signature   = md5("$class::$method");

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

            \Patchwork\redefine("$class::$method", function () use ($mock, $safe_method) {
                /** @phpstan-ignore-next-line */
                return call_user_func_array([$mock, $safe_method], func_get_args());
            });
        }

        return $mock->shouldReceive($safe_method);
    }

    /**
     * @param array|object $data The post data to add to the post
     *
     * @return \WP_Post
     */
    protected function mockPost($data)
    {
        /** @var \WP_Post $post */
        $post = \Mockery::mock('WP_Post');
        $data = array_merge(array(
            'ID'                => 0,
            'post_author'       => 0,
            'post_type'         => '',
            'post_title'        => '',
            'post_date'         => '',
            'post_date_gmt'     => '',
            'post_content'      => '',
            'post_excerpt'      => '',
            'post_status'       => '',
            'comment_status'    => '',
            'ping_status'       => '',
            'post_password'     => '',
            'post_parent'       => 0,
            'post_modified'     => '',
            'post_modified_gmt' => '',
            'comment_count'     => 0,
            'menu_order'        => 0,
        ), (array) $data);
        array_walk($data, function ($value, $prop) use ($post) {
            $post->$prop = $value;
        });

        return $post;
    }

    /**
     * @param array $query_vars
     *
     * @return \WP
     */
    protected function mockWp(array $query_vars = array())
    {
        /** @var \WP $wp */
        $wp             = \Mockery::mock('WP');
        $wp->query_vars = $query_vars;

        return $wp;
    }

    protected function cleanGlobals()
    {
        $common_globals = array(
            'post',
            'wp_query',
        );
        foreach ($common_globals as $var) {
            if (isset($GLOBALS[ $var ])) {
                unset($GLOBALS[ $var ]);
            }
        }
    }

    /**
     * Require any testFiles that are defined in a subclass
     *
     * This will only work if the WP_MOCK_INCLUDE_DIR is defined to point to the root directory you want to include
     * files from.
     */
    protected function requireFileDependencies()
    {
        if (! empty($this->testFiles) && defined('WP_MOCK_INCLUDE_DIR')) {
            foreach ($this->testFiles as $file) {
                if (file_exists(WP_MOCK_INCLUDE_DIR . $file)) {
                    require_once(WP_MOCK_INCLUDE_DIR . $file);
                }
            }
        }
    }

    protected function setUpContentFiltering()
    {
        $this->__contentFilterCallback = false;

        $annotations = TestUtil::parseTestMethodAnnotations(
            static::class,
            $this->getName(false)
        );
        if (
            ! isset($annotations['stripTabsAndNewlinesFromOutput']) ||
            $annotations['stripTabsAndNewlinesFromOutput'][0] !== 'disabled' ||
            (
                is_numeric($annotations['stripTabsAndNewlinesFromOutput'][0]) &&
                (int) $annotations['stripTabsAndNewlinesFromOutput'][0] !== 0
            )
        ) {
            $this->__contentFilterCallback = array( $this, 'stripTabsAndNewlines' );
            $this->setOutputCallback($this->__contentFilterCallback);
        }
    }

    public function run(TestResult $result = null): TestResult
    {
        if ($result === null) {
            $result = $this->createResult();
        }

        WP_Mock::getDeprecatedListener()->setTestResult($result);
        WP_Mock::getDeprecatedListener()->setTestCase($this);

        return parent::run($result);
    }

    /**
     * @after
     */
    public function checkDeprecatedCalls()
    {
        WP_Mock::getDeprecatedListener()->checkCalls();
        WP_Mock::getDeprecatedListener()->reset();
    }
}
