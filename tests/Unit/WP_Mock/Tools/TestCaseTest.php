<?php

namespace WP_Mock\Tests\Unit\WP_Mock\Tools;

use Exception;
use Generator;
use Mockery;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\ExpectationFailedException;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use WP_Mock;
use WP_Mock\Tests\WP_MockTestCase;
use WP_Mock\Tools\TestCase;

#[CoversClass(TestCase::class)]
#[AllowMockObjectsWithoutExpectations]
final class TestCaseTest extends WP_MockTestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testCanSetUpTests(): void
    {
        $_POST = 'test_post';
        $_GET = 'test_get';
        $_REQUEST = 'test_request';

        $instance = $this->createPartialMock(TestCase::class, ['requireFileDependencies', 'cleanGlobals']);

        $instance->expects($this->once())->method('requireFileDependencies');
        $instance->expects($this->once())->method('cleanGlobals');

        $instance->setUp();

        $this->assertSame([], $_POST);
        $this->assertSame([], $_GET);
        $this->assertSame([], $_REQUEST);
    }

    /**
     * @return void
     * @throws Exception|ReflectionException
     */
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testCanTearDownTests(): void
    {
        $wpMock = Mockery::mock('overload:WP_Mock');
        $wpMock->shouldReceive('tearDown');

        $instance = $this->createPartialMock(TestCase::class, ['cleanGlobals']);

        $instance->expects($this->once())
            ->method('cleanGlobals');

        $property = new ReflectionProperty($instance, 'mockedStaticMethods');
        $property->setAccessible(true);
        $property->setValue($instance, ['foo' => 'bar']);

        $instance->tearDown();

        $this->assertSame([], $property->getValue($instance));
    }

    /**
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanCleanGlobals(): void
    {
        global $post, $wp_query;

        $post = 'foo';
        $wp_query = 'bar';

        $instance = $this->createPartialMock(TestCase::class, []);
        $method = new ReflectionMethod($instance, 'cleanGlobals');
        $method->setAccessible(true);
        $method->invoke($instance);

        $this->assertNull($GLOBALS['post'] ?? null);
        $this->assertNull($GLOBALS['wp_query'] ?? null);
    }

    /**
     * @param bool $throwsException
     * @return void
     * @throws Exception
     */
    #[DataProvider('providerAssertActionsCalled')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testCanAssertExpectedActionsWereCalled(bool $throwsException): void
    {
        $instance = $this->createPartialMock(TestCase::class, []);

        /** @var Mockery\Mock $wpMock */
        $wpMock = Mockery::mock('overload:WP_Mock');
        $method = $wpMock->shouldReceive('assertActionsCalled');

        if ($throwsException) {
            /** @phpstan-ignore-next-line  */
            $method->andThrow(Exception::class);

            $this->expectException(ExpectationFailedException::class);
        }

        $instance->assertActionsCalled();
    }

    /** @see testCanAssertExpectedActionsWereCalled */
    public static function providerAssertActionsCalled(): Generator
    {
        yield 'Actions were not called' => [true];
        yield 'Actions were called' => [false];
    }

    /**
     * @param bool $throwsException
     * @return void
     * @throws Exception
     */
    #[DataProvider('providerAssertHooksAdded')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testCanAssertExpectedHooksWereAdded(bool $throwsException): void
    {
        $instance = $this->createPartialMock(TestCase::class, []);

        /** @var Mockery\Mock $wpMock */
        $wpMock = Mockery::mock('overload:WP_Mock');
        $method = $wpMock->shouldReceive('assertHooksAdded');

        if ($throwsException) {
            /** @phpstan-ignore-next-line  */
            $method->andThrow(Exception::class);

            $this->expectException(ExpectationFailedException::class);
        }

        $instance->assertHooksAdded();
    }

    /** @see testCanAssertExpectedHooksWereAdded */
    public static function providerAssertHooksAdded(): Generator
    {
        yield 'Hooks were not added' => [true];
        yield 'Hooks were added' => [false];
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCanAssertCurrentTestConditionsWereMet(): void
    {
        $instance = $this->createPartialMock(TestCase::class, ['assertConditionsMet']);

        $instance->expects($this->once())
            ->method('assertConditionsMet')
            ->with('test');

        $instance->assertCurrentConditionsMet('test');
    }

    /**
     * @return void
     */
    public function testCanAssertTestConditionsWereMet(): void
    {
        $instance = $this->createPartialMock(TestCase::class, []);

        // this will intentionally always pass
        $instance->assertConditionsMet('test');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCanAssertEqualsHtml(): void
    {
        $instance = $this->createPartialMock(TestCase::class, []);

        $instance->assertEqualsHtml('<p>test</p>', "<p>test</p>");

        $this->expectException(ExpectationFailedException::class);

        $instance->assertEqualsHtml('<p>foo</p>', '<p>bar</p>');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testCanAssertOutputEqualsHtml(): void
    {
        $instance = $this->createPartialMock(TestCase::class, []);

        // whitespace-insensitive match passes
        $instance->assertOutputEqualsHtml('<p>test</p>', static function () {
            echo "<p>\n\t test</p>";
        });

        // a mismatch throws
        $this->expectException(ExpectationFailedException::class);

        $instance->assertOutputEqualsHtml('<p>foo</p>', static function () {
            echo '<p>bar</p>';
        });
    }

    /**
     * @param bool $usingPatchwork
     * @param bool $invalidMethod
     * @return void
     * @throws ReflectionException|Exception
     */
    #[DataProvider('providerMockStaticMethod')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testCanMockStaticMethod(bool $usingPatchwork, bool $invalidMethod): void
    {
        $wpMock = Mockery::mock('overload:WP_Mock');
        /** @phpstan-ignore-next-line */
        $wpMock->shouldReceive('usingPatchwork')->andReturns($usingPatchwork);

        $class = new class () {
            public static function testMethod(): bool
            {
                return true;
            }
        };

        $this->assertTrue($class::testMethod());

        $instance = $this->createPartialMock(TestCase::class, []);
        $method = new ReflectionMethod($instance, 'mockStaticMethod');
        $method->setAccessible(true);

        if (! $usingPatchwork || $invalidMethod) {
            $this->expectException(Exception::class);
        }

        /** @var Mockery\Expectation $mockExpectation */
        $mockExpectation = $method->invokeArgs($instance, [get_class($class), $invalidMethod ? 'invalid' : 'testMethod']);
        $mockExpectation->once()->andReturnFalse();

        $this->assertFalse($class::testMethod());
    }

    /** @see testCanMockStaticMethod */
    public static function providerMockStaticMethod(): Generator
    {
        yield 'Patchwork is disabled' => [false, false];
        yield 'Referencing invalid method' => [true, true];
        yield 'Should mock method' => [true, false];
    }
}
