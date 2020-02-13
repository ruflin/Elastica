<?php

namespace Elastica\Test;

use Elastica\JSON;
use PHPUnit\Framework\TestCase;

/**
 * JSONTest.
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
class JSONTest extends TestCase
{
    public function testStringifyMustNotThrowExceptionOnValid()
    {
        JSON::stringify([]);
        $this->assertTrue(true);
    }

    public function testStringifyMustThrowExceptionNanOrInf()
    {
        $this->expectException(\Elastica\Exception\JSONParseException::class);
        $this->expectExceptionMessage('Inf and NaN cannot be JSON encoded');

        $arr = [NAN, INF];
        JSON::stringify($arr);
        $this->assertTrue(true);
    }

    public function testStringifyMustThrowExceptionMaximumDepth()
    {
        $this->expectException(\Elastica\Exception\JSONParseException::class);
        $this->expectExceptionMessage('Maximum stack depth exceeded');

        $arr = [[[]]];
        JSON::stringify($arr, 0, 0);
        $this->assertTrue(true);
    }
}
