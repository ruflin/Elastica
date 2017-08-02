<?php
namespace Elastica\Test\Filter;

use Elastica\Filter\Limit;
use Elastica\Test\Base as BaseTest;

class LimitTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testSetType()
    {
        $filter = new Limit(10);
        $this->assertEquals(10, $filter->getParam('value'));

        $this->assertInstanceOf('Elastica\Filter\Limit', $filter->setLimit(20));
        $this->assertEquals(20, $filter->getParam('value'));
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $filter = new Limit(15);

        $expectedArray = array(
            'limit' => array('value' => 15),
        );

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
