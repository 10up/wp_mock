<?php

namespace WP_Mock\Tests\Unit\WP_Mock;

use Exception;
use Generator;
use Mockery;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\RiskyTestError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use stdClass;
use WP_Mock;
use WP_Mock\DeprecatedMethodListener;
use WP_Mock\Tests\WP_MockTestCase;

/**
 * @covers \WP_Mock\DeprecatedMethodListener
 */
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
        $this->object = new DeprecatedMethodListener();
    }

    /**
     * Resets the deprecated method listener calls after each test.
     *
     * @return void
     */
    public function tearDown(): void
    {
        $this->object->reset();
    }

    /**
     * @covers \WP_Mock\DeprecatedMethodListener::setTestName()
     *
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
     * @covers \WP_Mock\DeprecatedMethodListener::setTestCase()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanSetTestCase(): void
    {
        /** @var TestCase&Mockery\MockInterface $testCase */
        $testCase = Mockery::mock(TestCase::class);

        $this->assertSame($this->object, $this->object->setTestCase($testCase));

        $property = new ReflectionProperty($this->object, 'testCase');
        $property->setAccessible(true);

        $this->assertSame($testCase, $property->getValue($this->object));
    }

    /**
     * @covers \WP_Mock\DeprecatedMethodListener::setTestResult()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanSetTestResult(): void
    {
        $concreteTestResult = new TestResult();
        /** @var TestResult&Mockery\MockInterface $mockTestResult @phpstan-ignore-line */
        $mockTestResult = Mockery::mock($concreteTestResult);

        $this->assertSame($this->object, $this->object->setTestResult($mockTestResult));

        $property = new ReflectionProperty($this->object, 'testResult');
        $property->setAccessible(true);

        $this->assertSame($mockTestResult, $property->getValue($this->object));
    }

    /**
     * @covers \WP_Mock\DeprecatedMethodListener::logDeprecatedCall()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanLogDeprecatedCall(): void
    {
        $method = 'Foo::bar'.rand(0, 9);
        $args = [rand(10, 99)];

        $this->assertSame($this->object, $this->object->logDeprecatedCall($method, $args));

        $this->assertEquals([[$method, $args]], $this->getDeprecatedMethodCalls($this->object));
    }

    /**
     * @covers \WP_Mock\DeprecatedMethodListener::reset()
     *
     * @return void
     * @throws Exception
     */
    public function testCanResetDeprecatedCallsLog(): void
    {
        $this->assertSame($this->object, $this->object->logDeprecatedCall('Foo::bar', ['baz']));
        $this->assertSame($this->object, $this->object->reset());
        $this->assertSame([], $this->getDeprecatedMethodCalls($this->object));
    }

    /**
     * @covers \WP_Mock\DeprecatedMethodListener::checkCalls()
     *
     * @return void
     * @throws Exception
     */
    public function testCheckDeprecatedMethodCallsWithNoCallsMade(): void
    {
        $concreteTestResult = new TestResult();
        /** @var TestResult&Mockery\MockInterface $mockTestResult @phpstan-ignore-line */
        $mockTestResult = Mockery::mock($concreteTestResult);
        $mockTestResult->expects('addFailure')->never();

        $this->object->setTestResult($mockTestResult);
        $this->object->checkCalls();

        $this->assertConditionsMet();
    }

    /**
     * @covers \WP_Mock\DeprecatedMethodListener::checkCalls()
     *
     * @return void
     */
    public function testCanCheckDeprecatedMethodCallsWithScalarArgs(): void
    {
        $this->object->logDeprecatedCall('FooBar::bazBat', ['string', true, 42]);
        $this->object->setTestName('TestName');

        /** @var TestCase&Mockery\MockInterface $mockTestCase */
        $mockTestCase = Mockery::mock(TestCase::class);

        $this->object->setTestCase($mockTestCase);

        $concreteTestResult = new TestResult();
        /** @var TestResult&Mockery\MockInterface $mockTestResult @phpstan-ignore-line */
        $mockTestResult = Mockery::mock($concreteTestResult)->makePartial();

        $mockTestResult->expects('addFailure')
            ->once()
            ->andReturnUsing(function ($concreteCase, $exception, $int) use ($mockTestCase) {
                $int = (int) $int; // It's coming as 0.0
                Assert::assertSame($mockTestCase, $concreteCase);
                Assert::assertTrue($exception instanceof RiskyTestError);
                $message = <<<EOT
Deprecated WP Mock calls inside TestName:
  FooBar::bazBat ["string",true,42]
EOT;
                Assert::assertEquals($message, $exception->getMessage());
                Assert::assertTrue(0 === $int);
            });

        $this->object->setTestResult($mockTestResult);
        $this->object->checkCalls();
    }

    /**
     * @covers \WP_Mock\DeprecatedMethodListener::checkCalls()
     *
     * @return void
     * @throws Exception
     */
    public function testCanCheckDeprecatedMethodCallsWithNonScalarArgs(): void
    {
        $object1 = Mockery::mock('WP_Query');
        $range = rand(5, 10);
        $resource = fopen('php://temp', 'r');
        $callback1 = function () {
        };

        $this->object->logDeprecatedCall('BazBat::fooBar', [$callback1]);
        $this->object->logDeprecatedCall('BazBat::fooBar', [$object1]);
        $this->object->logDeprecatedCall('BazBat::fooBar', [$object1]);
        $this->object->logDeprecatedCall('LongerClassName::callback', [[$object1, 'shouldReceive']]);
        $this->object->logDeprecatedCall('BazBat::fooBar', [range(1, $range), $resource]);
        $this->object->setTestName('OtherTest');

        /** @var TestCase&Mockery\MockInterface $mockTestCase @phpstan-ignore-line */
        $mockTestCase = Mockery::mock(TestCase::class);

        $this->object->setTestCase($mockTestCase);

        $concreteTestResult = new TestResult();
        /** @var TestResult&Mockery\MockInterface $mockTestResult @phpstan-ignore-line */
        $mockTestResult = Mockery::mock($concreteTestResult);

        $testClosure = function ($case, $exception, $int) use ($mockTestCase, $callback1, $object1, $range) {
            $int = (int) $int; // It's coming as 0.0
            $callback1 = get_class($callback1) . ':' . spl_object_hash($callback1);
            $object1   = get_class($object1) . ':' . spl_object_hash($object1);

            Assert::assertSame($mockTestCase, $case);
            Assert::assertTrue($exception instanceof RiskyTestError);

            $message = <<<EOT
Deprecated WP Mock calls inside OtherTest:
  BazBat::fooBar            ["<$callback1>"]
                            ["<$object1>"]
                            ["Array([$range] ...)","Resource"]
  LongerClassName::callback ["[<$object1>,shouldReceive]"]
EOT;
            Assert::assertEquals($message, $exception->getMessage());
            Assert::assertTrue(0 === $int);
        };

        $mockTestResult->expects('addFailure')
            ->once()
            ->andReturnUsing($testClosure);

        $this->object->setTestResult($mockTestResult);

        try {
            $this->object->checkCalls();
        } catch (Exception $exception) {
            fclose($resource); // @phpstan-ignore-line

            throw $exception;
        }

        fclose($resource); // @phpstan-ignore-line
    }

    /**
     * @covers \WP_Mock\DeprecatedMethodListener::buildErrorMessage()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanBuildErrorMessage(): void
    {
        $instance = new DeprecatedMethodListener();
        $instance->setTestName('MyTest');
        $instance->logDeprecatedCall('Foo::bar', ['baz']);

        $method = new ReflectionMethod($instance, 'buildErrorMessage');
        $method->setAccessible(true);

        $expectedMessage = 'Deprecated WP Mock calls inside MyTest:'."\n  ".'Foo::bar ["baz"]';

        $this->assertSame($expectedMessage, $method->invoke($instance));
    }

    /**
     * @covers \WP_Mock\DeprecatedMethodListener::getDeprecatedMethods()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanGetDeprecatedMethods(): void
    {
        $instance = new DeprecatedMethodListener();
        $instance->logDeprecatedCall('Foo::bar', ['baz']);
        $instance->logDeprecatedCall('Boz::qux');

        $method = new ReflectionMethod($instance, 'getDeprecatedMethods');
        $method->setAccessible(true);

        $this->assertSame(['Foo::bar', 'Boz::qux'], $method->invoke($instance));
    }

    /**
     * @covers \WP_Mock\DeprecatedMethodListener::getDeprecatedMethodsWithArgs()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanGetDeprecatedMethodsWithArgs(): void
    {
        $instance = new DeprecatedMethodListener();
        $instance->logDeprecatedCall('Foo::bar', ['baz']);
        $instance->logDeprecatedCall('Boz::qux');

        $method = new ReflectionMethod($instance, 'getDeprecatedMethodsWithArgs');
        $method->setAccessible(true);

        $expected = [
            'Foo::bar' => [
                '["baz"]'
            ],
            'Boz::qux' => [
                '[]'
            ],
        ];

        $this->assertSame($expected, $method->invoke($instance));
    }

    /**
     * @covers \WP_Mock\DeprecatedMethodListener::toScalar()
     * @dataProvider providerConvertsArgumentsToScalarValue
     *
     * @param mixed $arg
     * @param string|bool|null|float|int $expected
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanConvertArgumentsToScalarValue($arg, $expected): void
    {
        $instance = new DeprecatedMethodListener();
        $method = new ReflectionMethod($instance, 'toScalar');
        $method->setAccessible(true);

        $result = $method->invokeArgs($instance, [$arg]);

        if (is_object($arg) && is_string($expected) && is_string($result)) {
            $this->assertStringStartsWith($expected, $result);
        } else {
            $this->assertSame($expected, $result);
        }
    }

    /** @see testCanConvertArgumentsToScalarValue */
    public function providerConvertsArgumentsToScalarValue(): Generator
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
     * @covers \WP_Mock\DeprecatedMethodListener::logDeprecatedCall()
     * @covers \WP_Mock::getDeprecatedMethodListener()
     *
     * @return void
     * @throws Exception
     */
    public function testCanHandleDeprecatedMethodCall(): void
    {
        $deprecatedMethodListener = new DeprecatedMethodListener();

        $concreteTestResult = new TestResult();
        /** @var TestResult&Mockery\MockInterface $mockTestResult @phpstan-ignore-line */
        $mockTestResult = Mockery::mock($concreteTestResult);
        /** @var TestCase&Mockery\MockInterface $mockTestCase */
        $mockTestCase = Mockery::mock(TestCase::class);

        $instance = new class ($deprecatedMethodListener) extends WP_Mock {
            /**
             * @param DeprecatedMethodListener $deprecatedMethodListener
             */
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
        };

        $mockTestResult->expects('addFailure')
            ->once()
            ->with($mockTestCase, Mockery::type(RiskyTestError::class), 0);

        $deprecatedMethodListener->setTestCase($mockTestCase);
        $deprecatedMethodListener->setTestResult($mockTestResult);

        $instance->deprecatedMethod(['foo' => 'bar']);

        $deprecatedMethodListener->checkCalls();

        $this->assertConditionsMet();
    }

    /**
     * Gets logged deprecated method calls from a {@see DeprecatedMethodListener} instance.
     *
     * @see DeprecatedMethodListener::$deprecatedCalls
     *
     * @param DeprecatedMethodListener $listener
     * @return array<array{string, array<mixed>}>
     * @throws ReflectionException
     */
    protected function getDeprecatedMethodCalls(DeprecatedMethodListener $listener): array
    {
        $property = new ReflectionProperty($listener, 'deprecatedCalls');
        $property->setAccessible(true);

        $value = $property->getValue($listener);

        return is_array($value) ? $value : [];
    }
}
