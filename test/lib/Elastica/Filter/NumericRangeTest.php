<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Filter_NumericRangeTest extends PHPUnit_Framework_TestCase
{
    public function testAddField()
    {
        $rangeFilter = new Elastica_Filter_NumericRange();
        $returnValue = $rangeFilter->addField('fieldName', array('to' => 'value'));
        $this->assertInstanceOf('Elastica_Filter_NumericRange', $returnValue);
    }

    public function testToArray()
    {
        $filter = new Elastica_Filter_NumericRange();

        $fromTo = array('from' => 'ra', 'to' => 'ru');
        $filter->addField('name', $fromTo);

        $expectedArray = array(
            'numeric_range' => array(
                'name' => $fromTo
            )
        );

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
