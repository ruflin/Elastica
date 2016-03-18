<?php

namespace Elastica\Test\Query;

use Elastica\Query\NumericRange;
use Elastica\Test\Base as BaseTest;

class NumericRangeTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testAddField()
    {
        $rangeQuery = new NumericRange();
        $returnValue = $rangeQuery->addField('fieldName', array('to' => 'value'));
        $this->assertInstanceOf('Elastica\Query\NumericRange', $returnValue);
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $query = new NumericRange();

        $fromTo = array('from' => 'ra', 'to' => 'ru');
        $query->addField('name', $fromTo);

        $expectedArray = array(
            'numeric_range' => array(
                'name' => $fromTo,
            ),
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }
}
