<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastica\JSON;
use PHPUnit\Framework\TestCase;

/**
 * JSONTest.
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 *
 * @internal
 */
class JSONTest extends TestCase
{
    public function testStringifyMustNotThrowExceptionOnValid(): void
    {
        JSON::stringify([]);
        $this->assertTrue(true);
    }

    public function testStringifyMustThrowExceptionNanOrInf(): void
    {
        $this->expectException(\JsonException::class);
        $this->expectExceptionMessage('Inf and NaN cannot be JSON encoded');

        $arr = [\NAN, \INF];
        JSON::stringify($arr);
        $this->assertTrue(true);
    }

    public function testStringifyMustThrowExceptionMaximumDepth(): void
    {
        $this->expectException(\JsonException::class);
        $this->expectExceptionMessage('Maximum stack depth exceeded');

        $arr = [[[]]];
        JSON::stringify($arr, 0, 0);
        $this->assertTrue(true);
    }
}
