<?php
namespace Elastica\Test\Filter;

use Elastica\Filter\Type;
use Elastica\Test\Base as BaseTest;

class TypeTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testSetType()
    {
        $typeFilter = new Type();
        $returnValue = $typeFilter->setType('type_name');
        $this->assertInstanceOf('Elastica\Filter\Type', $returnValue);
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $typeFilter = new Type('type_name');

        $expectedArray = array(
            'type' => array('value' => 'type_name'),
        );

        $this->assertEquals($expectedArray, $typeFilter->toArray());
    }
}
