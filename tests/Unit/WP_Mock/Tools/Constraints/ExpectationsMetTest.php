<?php

namespace WP_Mock\Tests\Unit\WP_Mock\Tools\Constraints;

use WP_Mock\Tests\WP_MockTestCase;
use WP_Mock\Tools\Constraints\ExpectationsMet;

/**
 * @covers \WP_Mock\Tools\Constraints\ExpectationsMet
 */
final class ExpectationsMetTest extends WP_MockTestCase
{
    /**
     * @covers \WP_Mock\Tools\Constraints\ExpectationsMet::toString()
     *
     * @return void
     */
    public function testCanConvertToString() : void
    {
        $this->assertSame('WP_Mock expectations are met', (new ExpectationsMet())->toString());
    }
}
