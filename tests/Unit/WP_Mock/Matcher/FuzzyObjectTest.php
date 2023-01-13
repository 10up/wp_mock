<?php

namespace WP_Mock\Tests\Unit\WP_Mock\Matcher;

use Generator;
use Mockery;
use Mockery\Exception as MockeryException;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use WP_Mock\Matcher\FuzzyObject;
use WP_Mock\Tests\Mocks\SampleClass;
use WP_Mock\Tests\Mocks\SampleClassTwo;
use WP_Mock\Tests\Mocks\SampleSubClass;

class FuzzyObjectTest extends TestCase
{

    /**
     * @covers       \WP_Mock\Matcher\FuzzyObject::__construct()
     * @dataProvider providerCanConstruct
     *
     * @param object|array|mixed $expected
     * @param bool $shouldThrowException
     * @return void
     */
    public function testCanConstruct($expected, bool $shouldThrowException): void
    {
        if ($shouldThrowException) {
            $this->expectException(MockeryException::class);
        } else {
            $this->expectNotToPerformAssertions();
        }

        new FuzzyObject($expected);

    }

    /** @see testCanConstruct */
    public function providerCanConstruct(): Generator
    {
        yield 'Can construct when $expected is object' => [
            '$expected' => new SampleClass(),
            'should throw exception' => false
        ];
        yield 'Can construct when $expected is array' => [
            '$expected' => [],
            'should throw exception' => false];

        yield 'Exception when $expected is string' => [
            '$expected' => 'string',
            'should throw exception' => true
        ];

        yield 'Exception when $expected is int' => [
            '$expected' => 1,
            'should throw exception' => true
        ];

        yield 'Exception when $expected is float' => [
            '$expected' => 1.1,
            'should throw exception' => true
        ];

        yield 'Exception when $expected is bool' => [
            '$expected' => true,
            'should throw exception' => true
        ];

        yield 'Exception when $expected is null' => [
            '$expected' => null,
            'should throw exception' => true
        ];

    }

    /**
     * @covers \WP_Mock\Matcher\FuzzyObject::match()
     * @dataProvider providerCanMatch
     *
     * @param mixed $testClass
     * @param object $expectedClass
     * @param bool $expectedResult
     * @return void
     */
    public function testCanMatch(
        $testClass,
        object $expectedClass,
        bool $expectedResult
    ): void
    {

        $partialMock = Mockery::mock(FuzzyObject::class, [$expectedClass])
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();

        // test this function independently, return true for this test.
        $partialMock
            ->shouldReceive('haveCommonAncestor')
            ->with($testClass, $expectedClass)
            ->andReturnTrue();

        $this->assertSame(
            $expectedResult,
            $partialMock->match($testClass)
        );

        $this->assertPostConditions();
    }

    /** @see testCanMatch */
    public function providerCanMatch(): Generator
    {
        yield 'False when test class is not a class.' => [
            'testClass' => 'not a class',
            'expectedClass' => new SampleClass(),
            'expectedResult' => false,
        ];

        yield 'False when test class properties do not match expected class properties.' => [
            'testClass' => new class() {
                public $testProperty = 'test';
            },
            'expectedClass' => new SampleClass(),
            'expectedResult' => false,
        ];

        yield 'False when test class property values do not match expected class property values.' => [
            'testClass' => new class() {
                public $testProperty = 'test';
            },
            'expectedClass' => new class() {
                public $testProperty = 'not test';
            },
            'expectedResult' => false,
        ];

        yield 'False when actual class has more properties than test class.' => [
            'testClass' => new class() {
                public $testProperty = 'test';
            },
            'expectedClass' => new class() {
                public $testProperty = 'test';
                public $testProperty2 = 'test';
            },
            'expectedResult' => false,
        ];

        yield 'True when classes are identical' => [
            'testClass' => new SampleClass(),
            'expectedClass' => new SampleClass(),
            'expectedResult' => true,
        ];

        yield 'True when classes have identical properties' => [
            'testClass' => new class() {
                public $testProperty = 'test';
            },
            'expectedClass' => new class() {
                public $testProperty = 'test';
            },
            'expectedResult' => true,
        ];
    }

    /**
     * @covers \WP_Mock\Matcher\FuzzyObject::haveCommonAncestor()
     * @dataProvider providerCanDetermineHaveCommonAncestor
     *
     * @throws ReflectionException
     */
    public function testCanDetermineHaveCommonAncestor(
        $object1,
        $object2,
        bool $expectedResult): void
    {
        $instance = new FuzzyObject(new SampleClass());
        $method = new ReflectionMethod($instance, 'haveCommonAncestor');
        $method->setAccessible(true);
        $result = $method->invoke($instance, $object1, $object2);

        $this->assertSame($expectedResult, $result);
    }

    /** @see testCanDetermineHaveCommonAncestor */
    public function providerCanDetermineHaveCommonAncestor(): Generator
    {
        yield 'False when object1 is not an object' => [
            'object1' => 'not an object',
            'object2' => new SampleClass(),
            'expectedResult' => false,
        ];

        yield 'False when object2 is not an object' => [
            'object1' => new SampleClass(),
            'object2' => 'not an object',
            'expectedResult' => false,
        ];

        yield 'False when objects have no common ancestor' => [
            'object1' => new SampleSubClass(),
            'object2' => new SampleClassTwo(),
            'expectedResult' => false,
        ];

        yield 'True when both objects are identical' => [
            'object1' => new SampleClass(),
            'object2' => new SampleClass(),
            'expectedResult' => true,
        ];

        yield 'True when both object have common ancestor' => [
            'object1' => new SampleSubClass(),
            'object2' => new SampleClass(),
            'expectedResult' => true,
        ];
    }

    /**
     * @covers \WP_Mock\Matcher\FuzzyObject::__toString()
     * @dataProvider providerToString
     */
    public function testToString($object, string $expectedResult): void
    {
        $instance = new FuzzyObject($object);
        $this->assertEquals($expectedResult, $instance->__toString());
    }

    /** @see testToString */
    public function providerToString(): Generator
    {
        yield 'With expected object with all types of properties' => [
            'expected' => new class() {
                public $testPropertyIsArray = ['foo','bar'];

                public $testPropertyIsClass;

                public $testPropertyIsResource;

                public $testPropertyIsString = 'foo';

                public function __construct()
                {
                    $this->testPropertyIsClass = new SampleClass();
                    $this->testPropertyIsResource = stream_context_create();
                }
            },
            'expectedString' => '<FuzzyObject[Array, WP_Mock\Tests\Mocks\SampleClass, stream-context, foo]>',
        ];

        yield 'With expected object with no properties' => [
            'expected' => new class() {},
            'expectedString' => '<FuzzyObject[]>',
        ];

        yield 'With array' => [
            'expected' => ['foo','bar'],
            'expectedString' => '<FuzzyObject[foo, bar]>',
        ];
    }
}
