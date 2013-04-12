<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Document;
use Elastica\Query;
use Elastica\Query\Match;
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
}
