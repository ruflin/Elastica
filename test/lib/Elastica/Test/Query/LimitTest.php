<?php

namespace Elastica\Test\Query;

use Elastica\Query\Limit;
use Elastica\Test\Base as BaseTest;

class LimitTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testSetType()
    {
        $query = new Limit(10);
        $this->assertEquals(10, $query->getParam('value'));

        $this->assertInstanceOf('Elastica\Query\Limit', $query->setLimit(20));
        $this->assertEquals(20, $query->getParam('value'));
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $query = new Limit(15);

        $expectedArray = array(
            'limit' => array('value' => 15),
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }
}
