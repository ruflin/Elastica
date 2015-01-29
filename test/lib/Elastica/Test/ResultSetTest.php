<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Result;
use Elastica\Test\Base as BaseTest;

class ResultSetTest extends BaseTest
{
    public function testGetters()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $doc = new Document(1, array('name' => 'elastica search'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'elastica library'));
        $type->addDocument($doc);
        $doc = new Document(3, array('name' => 'elastica test'));
        $type->addDocument($doc);
        $index->refresh();

        $resultSet = $type->search('elastica search');

        $this->assertInstanceOf('Elastica\ResultSet', $resultSet);
        $this->assertEquals(3, $resultSet->getTotalHits());
        $this->assertGreaterThan(0, $resultSet->getMaxScore());
        $this->assertInternalType('array', $resultSet->getResults());
        $this->assertEquals(3, count($resultSet));
    }

    public function testArrayAccess()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $doc = new Document(1, array('name' => 'elastica search'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'elastica library'));
        $type->addDocument($doc);
        $doc = new Document(3, array('name' => 'elastica test'));
        $type->addDocument($doc);
        $index->refresh();

        $resultSet = $type->search('elastica search');

        $this->assertInstanceOf('Elastica\ResultSet', $resultSet);
        $this->assertInstanceOf('Elastica\Result', $resultSet[0]);
        $this->assertInstanceOf('Elastica\Result', $resultSet[1]);
        $this->assertInstanceOf('Elastica\Result', $resultSet[2]);

        $this->assertFalse(isset($resultSet[3]));
    }

    /**
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testInvalidOffsetCreation()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $doc = new Document(1, array('name' => 'elastica search'));
        $type->addDocument($doc);
        $index->refresh();

        $resultSet = $type->search('elastica search');

        $result = new Result(array('_id' => 'fakeresult'));
        $resultSet[1] = $result;
    }

    /**
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testInvalidOffsetGet()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $doc = new Document(1, array('name' => 'elastica search'));
        $type->addDocument($doc);
        $index->refresh();

        $resultSet = $type->search('elastica search');

        return $resultSet[3];
    }
}
