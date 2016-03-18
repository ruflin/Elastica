<?php

namespace Elastica\Test\Query;

use Elastica\Query\Exists;
use Elastica\Test\Base as BaseTest;

class ExistsTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $field = 'test';
        $query = new Exists($field);

        $expectedArray = array('exists' => array('field' => $field));
        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group unit
     */
    public function testSetField()
    {
        $field = 'test';
        $query = new Exists($field);

        $this->assertEquals($field, $query->getParam('field'));

        $newField = 'hello world';
        $this->assertInstanceOf('Elastica\Query\Exists', $query->setField($newField));

        $this->assertEquals($newField, $query->getParam('field'));
    }
}
