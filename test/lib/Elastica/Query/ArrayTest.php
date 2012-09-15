<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_ArrayTest extends PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        $testQuery = array('hello' => array('world'), 'name' => 'ruflin');
        $query = new Elastica_Query_Array($testQuery);

        $this->assertEquals($testQuery, $query->toArray());
    }
}
