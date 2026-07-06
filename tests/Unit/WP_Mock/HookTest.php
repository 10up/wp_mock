<?php

namespace WP_Mock\Tests\Unit\WP_Mock;

use Closure;
use Generator;
use Exception;
use Mockery;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;
use WP_Mock\Hook;
use WP_Mock\Traits\AccessInaccessibleClassMembersTrait;

#[CoversClass(Hook::class)]
#[AllowMockObjectsWithoutExpectations]
final class HookTest extends TestCase
{
    use AccessInaccessibleClassMembersTrait;

    /**
     * @param mixed $value
     * @param string $expected
     * @return void
     * @throws ReflectionException|Exception
     */
    #[DataProvider('providerSafeOffset')]
    public function testCanParseSafeOffSet($value, string $expected): void
    {
        $instance = $this->createPartialMock(Hook::class, ['new_responder', 'get_strict_mode_message']);
        $method = $this->getInaccessibleMethod($instance, 'safe_offset');

        $this->assertSame($expected, $method->invokeArgs($instance, [$value]));
    }

    /** @see testCanParseSafeOffset */
    public static function providerSafeOffset(): Generator
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
    }
}
