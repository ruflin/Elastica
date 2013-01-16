<?php

namespace Elastica\Test\Query;

use Elastica\Query\Simple;
use Elastica\Test\Base as BaseTest;

class SimpleTest extends BaseTest
{
    public function testToArray()
    {
        $testQuery = array('hello' => array('world'), 'name' => 'ruflin');
        $query = new Simple($testQuery);

        $this->assertEquals($testQuery, $query->toArray());
    }
}
