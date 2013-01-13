<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\ExistsFilter;
use Elastica\Test\Base as BaseTest;

class ExistsTest extends BaseTest
{
    public function testToArray()
    {
        $field = 'test';
        $filter = new ExistsFilter($field);

        $expectedArray = array('exists' => array('field' => $field));
        $this->assertEquals($expectedArray, $filter->toArray());
    }

    public function testSetField()
    {
        $field = 'test';
        $filter = new ExistsFilter($field);

        $this->assertEquals($field, $filter->getParam('field'));

        $newField = 'hello world';
        $this->assertInstanceOf('Elastica\Filter\ExistsFilter', $filter->setField($newField));

        $this->assertEquals($newField, $filter->getParam('field'));
    }
}
