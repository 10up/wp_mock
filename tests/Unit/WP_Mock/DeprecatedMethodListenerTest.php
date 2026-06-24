<?php

namespace WP_Mock\Tests\Unit\WP_Mock;

use Exception;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use stdClass;
use WP_Mock;
use WP_Mock\DeprecatedMethodListener;
use WP_Mock\Tests\WP_MockTestCase;

#[CoversClass(WP_Mock::class)]
#[CoversClass(DeprecatedMethodListener::class)]
final class DeprecatedMethodListenerTest extends WP_MockTestCase
{
    /** @var DeprecatedMethodListener */
    protected $object;

    /**
     * Sets up the deprecated method listener handler before the tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new DeprecatedMethodListener();
    }

    /**
     * Resets the deprecated method listener calls after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->object->reset();

        parent::tearDown();
    }

    /**
     * Captures the {@see E_USER_DEPRECATED} messages emitted while running $callback.
     *
     * A local error handler intercepts the notices so that PHPUnit's
     * `failOnDeprecation` does not interfere with the assertions.
     *
     * @param callable $callback
     * @return string[] the captured deprecation messages, in order
     */
    protected function captureDeprecations(callable $callback): array
    {
        $messages = [];

        set_error_handler(static function (int $errno, string $errstr) use (&$messages): bool {
            $messages[] = $errstr;

            return true;
        }, E_USER_DEPRECATED);

        try {
            $callback();
        } finally {
            restore_error_handler();
        }

        return $messages;
    }

    /**
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanSetTestName(): void
    {
        $property = new ReflectionProperty($this->object, 'testName');
        $property->setAccessible(true);

        $this->assertSame('test', $property->getValue($this->object));

        $this->assertSame($this->object, $this->object->setTestName('FooBar'));

        $this->assertSame('FooBar', $property->getValue($this->object));
    }

    /**
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testLogDeprecatedCallRecordsAndTriggersDeprecation(): void
    {
        $method = 'Foo::bar';
        $args = [42];

        $messages = $this->captureDeprecations(function () use ($method, $args) {
            $this->assertSame($this->object, $this->object->logDeprecatedCall($method, $args));
        });

        // the call is recorded for inspection
        $this->assertSame([[$method, $args]], $this->getDeprecatedMethodCalls($this->object));

        // exactly one E_USER_DEPRECATED was emitted, mentioning the method and its args
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('Foo::bar', $messages[0]);
        $this->assertStringContainsString('[42]', $messages[0]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testDeprecationMessageIncludesTestNameAndArgs(): void
    {
        $this->object->setTestName('MyTest');

        $messages = $this->captureDeprecations(function () {
            $this->object->logDeprecatedCall('Foo::bar', ['baz']);
        });

        $this->assertCount(1, $messages);
        $this->assertSame('Deprecated WP_Mock call inside MyTest: Foo::bar ["baz"]', $messages[0]);
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCanResetDeprecatedCallsLog(): void
    {
        $this->captureDeprecations(function () {
            $this->object->logDeprecatedCall('Foo::bar', ['baz']);
        });

        $this->assertSame($this->object, $this->object->reset());
        $this->assertSame([], $this->getDeprecatedMethodCalls($this->object));
    }

    /**
     * @param mixed $arg
     * @param string|bool|null|float|int $expected
     * @return void
     * @throws ReflectionException|Exception
     */
    #[DataProvider('providerConvertsArgumentsToScalarValue')]
    public function testCanConvertArgumentsToScalarValue($arg, $expected): void
    {
        $instance = new DeprecatedMethodListener();
        $method = new ReflectionMethod($instance, 'toScalar');
        $method->setAccessible(true);

        $result = $method->invokeArgs($instance, [$arg]);

        if (is_object($arg) && is_string($expected) && '' !== $expected && is_string($result)) {
            $this->assertStringStartsWith($expected, $result);
        } else {
            $this->assertSame($expected, $result);
        }
    }

    /** @see testCanConvertArgumentsToScalarValue */
    public static function providerConvertsArgumentsToScalarValue(): Generator
    {
        yield 'null' => [null, null];
        yield 'true' => [true, true];
        yield 'false' => [false, false];
        yield 'int' => [42, 42];
        yield 'float' => [42.42, 42.42];
        yield 'string' => ['foo', 'foo'];
        yield 'array' => [[1, 2, 3], 'Array([3] ...)'];
        yield 'object' => [new stdClass(), '<stdClass:'];
        yield 'resource' => [fopen('php://temp', 'r'), 'Resource'];
        yield 'closure' => [function () {
        }, '<Closure:'];
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCanHandleDeprecatedMethodCallThroughWpMock(): void
    {
        $deprecatedMethodListener = new DeprecatedMethodListener();

        // Use a named fixture (defined below) rather than an anonymous class: anonymous class
        // names embed a null byte that trigger_error() truncates on PHP <= 8.1, which would drop
        // the method name from the deprecation message.
        $instance = new WpMockWithDeprecatedMethod($deprecatedMethodListener);

        $result = null;

        $messages = $this->captureDeprecations(function () use ($instance, &$result) {
            $result = $instance->deprecatedMethod(['foo' => 'bar']);
        });

        // the deprecated method still returns normally (a deprecation is a notice, not a hard stop)
        $this->assertSame('test', $result);
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('deprecatedMethod', $messages[0]);
    }

    /**
     * Gets logged deprecated method calls from a {@see DeprecatedMethodListener} instance.
     *
     * @see DeprecatedMethodListener::$deprecatedCalls
     *
     * @param DeprecatedMethodListener $listener
     * @return array<array{string, array<mixed>}> the logged [$method, $args] pairs
     * @throws ReflectionException
     */
    protected function getDeprecatedMethodCalls(DeprecatedMethodListener $listener): array
    {
        $property = new ReflectionProperty($listener, 'deprecatedCalls');
        $property->setAccessible(true);

        $value = $property->getValue($listener);

        if (! is_array($value)) {
            return [];
        }

        /** @var array<array{string, array<mixed>}> $value */
        return $value;
    }
}

/**
 * Named WP_Mock subclass with a deprecated method, used by
 * {@see DeprecatedMethodListenerTest::testCanHandleDeprecatedMethodCallThroughWpMock()}.
 *
 * Intentionally a named (not anonymous) class so `__METHOD__` is stable and free of the null byte
 * that anonymous class names embed — which `trigger_error()` truncates on PHP <= 8.1, dropping the
 * method name from the captured deprecation message.
 */
final class WpMockWithDeprecatedMethod extends WP_Mock
{
    public function __construct(DeprecatedMethodListener $deprecatedMethodListener)
    {
        static::$deprecatedMethodListener = $deprecatedMethodListener;
    }

    /**
     * @param array<mixed> $args
     * @return string
     */
    public function deprecatedMethod(array $args = []): string
    {
        static::getDeprecatedMethodListener()->logDeprecatedCall(__METHOD__, $args);

        return 'test';
    }
}
