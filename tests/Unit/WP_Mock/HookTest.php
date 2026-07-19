<?php

namespace WP_Mock\Tests\Unit\WP_Mock;

use Closure;
use Generator;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;
use WP_Mock\Functions;
use WP_Mock\Hook;
use WP_Mock\Tests\Mocks\SampleClass;
use WP_Mock\Tests\Mocks\SampleSubClass;
use WP_Mock\Traits\AccessInaccessibleClassMembersTrait;

/**
 * @covers \WP_Mock\Hook
 */
final class HookTest extends TestCase
{
    use AccessInaccessibleClassMembersTrait;

    /**
     * @covers \WP_Mock\Hook::safe_offset()
     * @dataProvider providerSafeOffset
     *
     * @param mixed $value
     * @param string $expected
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanParseSafeOffSet($value, string $expected): void
    {
        $instance = $this->getMockForAbstractClass(Hook::class, [], '', false);
        $method = $this->getInaccessibleMethod($instance, 'safe_offset');

        $this->assertSame($expected, $method->invokeArgs($instance, [$value]));
    }

    /** @see testCanParseSafeOffset */
    public function providerSafeOffset(): Generator
    {
        $callbackInstance = new class () {
            public function callback(): bool
            {
                return true;
            }
        };

        $closureInstance = function () {
        };

        $objectInstance = new stdClass();

        yield 'null' => [null, 'null'];
        yield 'closure (object)' => [$closureInstance, '__CLOSURE__'];
        yield 'closure (representation)' => ['<Closure>', '__CLOSURE__'];
        yield 'closure (class name)' => [Closure::class, '__CLOSURE__'];
        yield 'closure (Mockery matcher)' => [Mockery::type(Closure::class), '__CLOSURE__'];
        yield 'scalar (string)' => ['test-string', 'test-string'];
        yield 'scalar (integer)' => [123, '123'];
        yield 'scalar (float)' => [1.23, '1.23'];
        yield 'scalar (true)' => [true, '1'];
        yield 'scalar (false)' => [false, ''];
        yield 'object' => [$objectInstance, spl_object_hash($objectInstance)];
        yield 'array (callback)' => [[$callbackInstance, 'callback'], spl_object_hash($callbackInstance).'callback'];
        yield 'type matcher (class)' => [Mockery::type(SampleClass::class), (string) Mockery::type(SampleClass::class)];
    }

    /**
     * @covers \WP_Mock\Hook::safe_offset()
     * @covers \WP_Mock\Functions::type()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testTypeSafeOffsetIsStableAcrossMultipleTypeCalls(): void
    {
        Hook::$objects = [];

        $instance = $this->getMockForAbstractClass(Hook::class, [], '', false);
        $method = $this->getInaccessibleMethod($instance, 'safe_offset');

        $typeKey1 = $method->invokeArgs($instance, [Functions::type(SampleClass::class)]);
        unset($typeKey1);
        gc_collect_cycles();

        $typeSampleClass = Functions::type(SampleClass::class);
        $typeSampleSubClass = Functions::type(SampleSubClass::class);

        $keyClass = $method->invokeArgs($instance, [$typeSampleClass]);
        $keySubClass = $method->invokeArgs($instance, [$typeSampleSubClass]);
        $keyInstance = $method->invokeArgs($instance, [new SampleClass()]);
        $keySubInstance = $method->invokeArgs($instance, [new SampleSubClass()]);
        $keyCallbackClass = $method->invokeArgs($instance, [[$typeSampleClass, 'action']]);
        $keyCallbackSubClass = $method->invokeArgs($instance, [[$typeSampleSubClass, 'action']]);

        $this->assertNotSame($keyClass, $keySubClass);
        $this->assertSame($keyClass, $keyInstance);
        $this->assertSame($keySubClass, $keySubInstance);
        $this->assertNotSame($keyCallbackClass, $keyCallbackSubClass);
        $this->assertSame((string) $typeSampleClass, $keyClass);
        $this->assertSame((string) $typeSampleSubClass, $keySubClass);

        Hook::$objects = [];
    }
}
