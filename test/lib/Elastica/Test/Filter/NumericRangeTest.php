<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\NumericRange;
use Elastica\Test\Base as BaseTest;

class NumericRangeTest extends BaseTest
{
    public function testAddField()
    {
        $rangeFilter = new NumericRange();
        $returnValue = $rangeFilter->addField('fieldName', array('to' => 'value'));
        $this->assertInstanceOf('Elastica\Filter\NumericRange', $returnValue);
    }

    public function testToArray()
    {
        $filter = new NumericRange();

        $fromTo = array('from' => 'ra', 'to' => 'ru');
        $filter->addField('name', $fromTo);

        $expectedArray = array(
            'numeric_range' => array(
                'name' => $fromTo,
            ),
        );

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
