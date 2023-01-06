<?php

namespace WP_Mock\Tests\Unit\WP_Mock\Traits;

use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use WP_Mock\Traits\AccessInaccessibleClassMembersTrait;

/**
 * @covers \WP_Mock\Traits\AccessInaccessibleClassMembersTrait
 */
final class AccessInaccessibleClassMembersTraitTest extends TestCase
{
    /**
     * @covers \WP_Mock\Traits\AccessInaccessibleClassMembersTrait::getInaccessibleProperty()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanGetInaccessibleProperty(): void
    {
        $instance = $this->getInstance('test');
        /* @phpstan-ignore-next-line */
        $property = $instance->getInaccessibleProperty($instance, 'property');

        $this->assertEquals('property', $property->getName());
        $this->assertEquals('test', $property->getValue($instance));
    }

    /**
     * @covers \WP_Mock\Traits\AccessInaccessibleClassMembersTrait::setInaccessibleProperty()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testCanSetInaccessibleProperty(): void
    {
        $instance = $this->getInstance('foo');
        $property = $instance->setInaccessibleProperty($instance, get_class($instance), 'property', 'bar');

        $this->assertEquals('bar', $property->getValue($instance));
    }

    /**
     * @covers \WP_Mock\Traits\AccessInaccessibleClassMembersTrait::getInaccessibleMethod()
     *
     * @return void
     * @throws Exception
     */
    public function testCanGetInaccessibleMethod(): void
    {
        $instance = $this->getInstance('test');
        $method = $instance->getInaccessibleMethod($instance, 'method');

        $this->assertEquals('method', $method->getName());
        $this->assertEquals('test', $method->invoke($instance));
    }

    /**
     * Gets the instance of an object implementing the trait.
     *
     * @param mixed $value value to be set on a private member and returned by an inaccessible method
     * @phpstan-ignore-next-line
     */
    protected function getInstance($value)
    {
        /** @phpstan-ignore-next-line */
        return new class($value) {
            use AccessInaccessibleClassMembersTrait;

            /** @var mixed */
            private $property;

            /**
             * @param mixed $value
             */
            public function __construct($value)
            {
                $this->property = $value;
            }

            /**
             * @return mixed
             * @phpstan-ignore-next-line
             */
            private function method()
            {
                return $this->property;
            }
        };
    }
}
