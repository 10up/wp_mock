<?php

namespace WP_Mock\Tests\Unit\WP_Mock\Matcher;

use WP_Mock\Matcher\AnyInstance;
use WP_Mock\Tests\Mocks\SampleClass;
use WP_Mock\Tests\Mocks\SampleSubClass;
use WP_Mock\Tests\WP_MockTestCase;

class AnyInstanceTest extends WP_MockTestCase
{
    /**
     * @covers \WP_Mock\Matcher\AnyInstance::match
     */
    public function testExactClassInstanceMatchesTrue()
    {
        $sut = new AnyInstance(new SampleClass());

        $exactClassAction = new SampleClass();

        $result = $sut->match($exactClassAction);

        $this->assertTrue($result);
    }

    /**
     * @covers \WP_Mock\Matcher\AnyInstance::match
     */
    public function testExactClassStringMatchesTrue()
    {
        $sut = new AnyInstance(SampleClass::class);

        $exactClassAction = new SampleClass();

        $result = $sut->match($exactClassAction);

        $this->assertTrue($result);
    }

    /**
     * @covers \WP_Mock\Matcher\AnyInstance::match
     */
    public function testSubClassMatchesTrue()
    {
        $sut = new AnyInstance(SampleClass::class);

        $subClassAction = new SampleSubClass();

        $result = $sut->match($subClassAction);

        $this->assertTrue($result);
    }

    /**
     * @covers \WP_Mock\Matcher\AnyInstance::match
     */
    public function testWrongClassMatchesFalse()
    {
        $sut = new AnyInstance(SampleClass::class);

        $wrongClassAction = new \stdClass();

        $result = $sut->match($wrongClassAction);

        $this->assertFalse($result);
    }

    /**
     * @covers \WP_Mock\Matcher\AnyInstance::match
     */
    public function testClosureMatchesFalse()
    {
        $sut = new AnyInstance(SampleClass::class);

        $closureAction = function () {
        };

        $result = $sut->match($closureAction);

        $this->assertFalse($result);
    }

    /**
     * @covers \WP_Mock\Matcher\AnyInstance::match
     */
    public function testStringFunctionMatchesFalse()
    {
        $sut = new AnyInstance(SampleClass::class);

        $stringFunctionAction = 'action_name';

        $result = $sut->match($stringFunctionAction);

        $this->assertFalse($result);
    }

    /**
     * @covers \WP_Mock\Matcher\AnyInstance::__toString
     */
    public function testToString()
    {
        $sut = new AnyInstance(SampleClass::class);

        $result = "$sut";

        $this->assertEquals("<AnyInstance[WP_Mock\Tests\Mocks\SampleClass]>", $result);
    }

    /**
     * @covers \WP_Mock\Matcher\AnyInstance::__construct
     */
    public function testCannotConstructWithoutObject()
    {
        $this->expectException(\Exception::class);

        new AnyInstance('NotAClass');
    }
}
