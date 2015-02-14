<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\MatchAll;
use Elastica\Search;
use Elastica\Test\Base as BaseTest;

class MatchAllTest extends BaseTest
{
    public function testToArray()
    {
        $query = new MatchAll();

        $expectedArray = array('match_all' => new \stdClass());

        $this->assertEquals($expectedArray, $query->toArray());
    }

    public function testMatchAllIndicesTypes()
    {
        $index1 = $this->_createIndex();
        $index2 = $this->_createIndex();

        $client = $index1->getClient();

        $search1 = new Search($client);
        $resultSet1 = $search1->search(new MatchAll());

        $doc1 = new Document(1, array('name' => 'ruflin'));
        $doc2 = new Document(1, array('name' => 'ruflin'));
        $index1->getType('test')->addDocument($doc1);
        $index2->getType('test')->addDocument($doc2);

        $index1->refresh();
        $index2->refresh();

        $search2 = new Search($client);
        $resultSet2 = $search2->search(new MatchAll());

        $this->assertEquals($resultSet1->getTotalHits() + 2, $resultSet2->getTotalHits());
    }
}
