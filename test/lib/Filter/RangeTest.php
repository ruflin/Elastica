<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\Range;
use Elastica\Test\Base as BaseTest;

class RangeTest extends BaseTest
{
    public function testAddField()
    {
        $rangeFilter = new Range();
        $returnValue = $rangeFilter->addField('fieldName', array('to' => 'value'));
        $this->assertInstanceOf('Elastica\Filter\Range', $returnValue);
    }

    public function testToArray()
    {
        $filter = new Range();

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
