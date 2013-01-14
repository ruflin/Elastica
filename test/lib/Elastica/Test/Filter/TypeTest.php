<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\TypeFilter;
use Elastica\Test\Base as BaseTest;

class TypeTest extends BaseTest
{
    public function testSetType()
    {
        $typeFilter = new TypeFilter();
        $returnValue = $typeFilter->setType('type_name');
        $this->assertInstanceOf('Elastica\Filter\TypeFilter', $returnValue);
    }

    public function testToArray()
    {
        $typeFilter = new TypeFilter('type_name');

        $expectedArray = array(
            'type' => array('value' => 'type_name')
        );

        $this->assertEquals($expectedArray, $typeFilter->toArray());
    }
}
