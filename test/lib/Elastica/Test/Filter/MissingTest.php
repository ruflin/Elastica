<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\Missing;
use Elastica\Test\Base as BaseTest;

class MissingTest extends BaseTest
{
    public function testToArray()
    {
        $filter = new Missing('field_name');
        $expectedArray = array('missing' => array('field' => 'field_name'));
        $this->assertEquals($expectedArray, $filter->toArray());

        $filter = new Missing('field_name');
        $filter->setExistence(true);
        $expectedArray = array('missing' => array('field' => 'field_name', 'existence' => true));
        $this->assertEquals($expectedArray, $filter->toArray());

        $filter = new Missing('field_name');
        $filter->setNullValue(true);
        $expectedArray = array('missing' => array('field' => 'field_name', 'null_value' => true));
        $this->assertEquals($expectedArray, $filter->toArray());
    }

    public function testSetField()
    {
        $filter = new Missing('field_name');

        $this->assertEquals('field_name', $filter->getParam('field'));

        $filter->setField('new_field_name');
        $this->assertEquals('new_field_name', $filter->getParam('field'));

        $returnValue = $filter->setField('very_new_field_name');
        $this->assertInstanceOf('Elastica\Filter\Missing', $returnValue);
    }

    public function testSetExistence()
    {
        $filter = new Missing('field_name');

        $filter->setExistence(true);
        $this->assertTrue($filter->getParam('existence'));

        $filter->setExistence(false);
        $this->assertFalse($filter->getParam('existence'));

        $returnValue = $filter->setExistence(true);
        $this->assertInstanceOf('Elastica\Filter\Missing', $returnValue);
    }

    public function testSetNullValue()
    {
        $filter = new Missing('field_name');

        $filter->setNullValue(true);
        $this->assertTrue($filter->getParam('null_value'));

        $filter->setNullValue(false);
        $this->assertFalse($filter->getParam('null_value'));

        $returnValue = $filter->setNullValue(true);
        $this->assertInstanceOf('Elastica\Filter\Missing', $returnValue);
    }
}
