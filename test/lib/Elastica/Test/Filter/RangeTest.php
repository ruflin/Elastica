<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\RangeFilter;
use Elastica\Test\Base as BaseTest;

class RangeTest extends BaseTest
{
    public function testAddField()
    {
        $rangeFilter = new RangeFilter();
        $returnValue = $rangeFilter->addField('fieldName', array('to' => 'value'));
        $this->assertInstanceOf('Elastica\Filter\RangeFilter', $returnValue);
    }

    public function testToArray()
    {
        $filter = new RangeFilter();

        $fromTo = array('from' => 'ra', 'to' => 'ru');
        $filter->addField('name', $fromTo);

        $expectedArray = array(
            'range' => array(
                'name' => $fromTo
            )
        );

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
