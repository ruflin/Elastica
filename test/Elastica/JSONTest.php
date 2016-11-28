<?php
namespace Elastica;

/**
 * JSONTest
 *
 * @author Oleg Andreyev <oleg.andreyev@intexsys.lv>
 */
class JSONTest extends \PHPUnit_Framework_TestCase
{
    public function testStringifyMustNotThrowExceptionOnValid()
    {
        JSON::stringify(array());
    }

    /**
     * @expectedException \Elastica\Exception\JSONParseException
     * @expectedExceptionMessage Inf and NaN cannot be JSON encoded
     */
    public function testStringifyMustThrowExceptionNanOrInf()
    {
        $arr = array(NAN, INF);
        JSON::stringify($arr);
    }

    /**
     * @expectedException \Elastica\Exception\JSONParseException
     * @expectedExceptionMessage Maximum stack depth exceeded
     */
    public function testStringifyMustThrowExceptionMaximumDepth()
    {
        $arr = array(array(array()));
        JSON::stringify($arr, 0, 0);
    }
}
