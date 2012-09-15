<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Filter_MatchAllTest extends PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        $filter = new Elastica_Filter_MatchAll();

        $expectedArray = array('match_all' => new stdClass());

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
