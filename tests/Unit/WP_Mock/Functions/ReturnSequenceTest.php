<?php

namespace WP_Mock\Tests\Unit\WP_Mock\Functions;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionProperty;
use WP_Mock\Functions\ReturnSequence;
use WP_Mock\Tests\WP_MockTestCase;

#[CoversClass(ReturnSequence::class)]
final class ReturnSequenceTest extends WP_MockTestCase
{
    /**
     * @return void
     * @throws Exception
     */
    public function testConstructor(): void
    {
        $returnSequence = new ReturnSequence('foo', 'bar');
        $property = new ReflectionProperty(ReturnSequence::class, 'returnValues');
        $property->setAccessible(true);

        $this->assertSame(['foo', 'bar'], $property->getValue($returnSequence));
    }

    /**
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
