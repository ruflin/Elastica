<?php
namespace Elastica\Test\Query;

use Bonami\Elastica\Query\Simple;
use Bonami\Elastica\Test\Base as BaseTest;

class SimpleTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $testQuery = array('hello' => array('world'), 'name' => 'ruflin');
        $query = new Simple($testQuery);

        $this->assertEquals($testQuery, $query->toArray());
    }
}
