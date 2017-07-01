<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\MatchNone;
use Elastica\Search;
use Elastica\Test\Base as BaseTest;

class MatchNoneTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $query = new MatchNone();

        $expectedArray = ['match_none' => new \stdClass()];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group functional
     */
    public function testMatchNone()
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $doc = new Document(1, ['name' => 'ruflin']);
        $index->getType('test')->addDocument($doc);

        $index->refresh();

        $search = new Search($client);
        $resultSet = $search->search(new MatchNone());

        $this->assertEquals(0, $resultSet->getTotalHits());
    }
}
