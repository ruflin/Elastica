<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_RangeTest extends PHPUnit_Framework_TestCase
{
    public function testQuery()
    {
        $client = new Elastica_Client();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('test');

        $doc = new Elastica_Document(1, array('age' => 16, 'height' => 140));
        $type->addDocument($doc);
        $doc = new Elastica_Document(2, array('age' => 21, 'height' => 155));
        $type->addDocument($doc);
        $doc = new Elastica_Document(3, array('age' => 33, 'height' => 160));
        $type->addDocument($doc);
        $doc = new Elastica_Document(4, array('age' => 68, 'height' => 160));
        $type->addDocument($doc);

        $index->optimize();
        $index->refresh();

        $query = new Elastica_Query_Range('age', array('from' => 10, 'to' => 20));
        $result = $type->search($query)->count();
        $this->assertEquals(1, $result);

        $query = new Elastica_Query_Range();
        $query->addField('height', array('gte' => 160));

        $result = $type->search($query)->count();
        $this->assertEquals(2, $result);
    }

    public function testToArray()
    {
        $range = new Elastica_Query_Range();

        $field = array('from' => 20, 'to' => 40);
        $range->addField('age', $field);

        $expectedArray = array(
            'range' => array(
                'age' => $field,
            )
        );

        $this->assertEquals($expectedArray, $range->toArray());
    }

    public function testConstruct()
    {
        $ranges = array('from' => 20, 'to' => 40);
        $range = new Elastica_Query_Range(
            'age',
            $ranges
        );

        $expectedArray = array(
            'range' => array(
                'age' => $ranges,
            )
        );

        $this->assertEquals($expectedArray, $range->toArray());
    }
}
