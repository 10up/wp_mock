<?php

namespace WP_Mock\Tests\Unit\WP_Mock\Functions;

use Exception;
use ReflectionException;
use ReflectionProperty;
use WP_Mock\Functions\ReturnSequence;
use WP_Mock\Tests\WP_MockTestCase;

/**
 * @covers \WP_Mock\Functions\ReturnSequence
 */
final class ReturnSequenceTest extends WP_MockTestCase
{
    /**
     * @covers \WP_Mock\Functions\ReturnSequence::__construct()
     *
     * @return void
     * @throws ReflectionException|Exception
     */
    public function testConstructor(): void
    {
        $returnSequence = new ReturnSequence('foo', 'bar');
        $property = new ReflectionProperty(ReturnSequence::class, 'returnValues');

        $this->assertSame(['foo', 'bar'], $property->getValue($returnSequence));
    }

    /**
     * @covers \WP_Mock\Functions\ReturnSequence::getReturnValues()
     * @covers \WP_Mock\Functions\ReturnSequence::setReturnValues()
     *
     * @return void
     * @throws Exception
     */
    public function testAccessors(): void
    {
        $returnSequence = new ReturnSequence('foo', 'bar');

        $this->assertSame(['foo', 'bar'], $returnSequence->getReturnValues());

        $this->assertInstanceOf(ReturnSequence::class, $returnSequence->setReturnValues(['baz' => 'boz']));

        $this->assertSame(['boz'], $returnSequence->getReturnValues());

        $this->assertInstanceOf(ReturnSequence::class, $returnSequence->setReturnValues('qux'));

        $this->assertSame(['qux'], $returnSequence->getReturnValues());
    }
}
