<?php

namespace Elastica\Test\Query;

use Elastica\Query\ArrayQuery;
use Elastica\Test\Base as BaseTest;

class ArrayTest extends BaseTest
{
    public function testToArray()
    {
        $testQuery = array('hello' => array('world'), 'name' => 'ruflin');
        $query = new ArrayQuery($testQuery);

        $this->assertEquals($testQuery, $query->toArray());
    }
}
