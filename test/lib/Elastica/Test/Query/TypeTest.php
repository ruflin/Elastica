<?php

namespace Elastica\Test\Query;

use Elastica\Query\Type;
use Elastica\Test\Base as BaseTest;

class TypeTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testSetType()
    {
        $typeQuery = new Type();
        $returnValue = $typeQuery->setType('type_name');
        $this->assertInstanceOf('Elastica\Query\Type', $returnValue);
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $typeQuery = new Type('type_name');

        $expectedArray = array(
            'type' => array('value' => 'type_name'),
        );

        $this->assertEquals($expectedArray, $typeQuery->toArray());
    }
}
