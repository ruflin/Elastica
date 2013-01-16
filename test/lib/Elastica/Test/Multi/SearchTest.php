<?php

namespace Elastica\Test\Multi;

use Elastica\Document;
use Elastica\Multi\Search as MultiSearch;
use Elastica\Query;
use Elastica\Query\Range;
use Elastica\Query\Term;
use Elastica\Search;
use Elastica\Test\Base as BaseTest;

class SearchTest extends BaseTest
{
    /**
     * @return \Elastica\Type
     */
    protected function _createType()
    {
        $client = $this->_getClient();

        $index = $client->getIndex('zero');
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);

        $docs = array();
        $docs[] = new Document(1, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(2, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(3, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(4, array('id' => 1, 'email' => 'test@test.com', 'username' => 'kate'));
        $docs[] = new Document(5, array('id' => 1, 'email' => 'test@test.com', 'username' => 'kate'));
        $docs[] = new Document(6, array('id' => 1, 'email' => 'test@test.com', 'username' => 'bunny'));
        $docs[] = new Document(7, array('id' => 1, 'email' => 'test@test.com', 'username' => 'bunny'));
        $docs[] = new Document(8, array('id' => 1, 'email' => 'test@test.com', 'username' => 'bunny'));
        $docs[] = new Document(9, array('id' => 1, 'email' => 'test@test.com', 'username' => 'bunny'));
        $docs[] = new Document(10, array('id' => 1, 'email' => 'test@test.com', 'username' => 'bunny'));
        $docs[] = new Document(11, array('id' => 1, 'email' => 'test@test.com', 'username' => 'bunny'));
        $type = $index->getType('zeroType');
        $type->addDocuments($docs);
        $index->refresh();

        return $type;
    }

    public function testConstruct()
    {
        $client = $this->_getClient();
        $multiSearch = new MultiSearch($client);

        $this->assertInstanceOf('Elastica\Multi\Search', $multiSearch);
        $this->assertSame($client, $multiSearch->getClient());
    }

    public function testSetSearches()
    {
        $client = $this->_getClient();
        $multiSearch = new MultiSearch($client);

        $search1 = new Search($client);
        $search2 = new Search($client);
        $search3 = new Search($client);

        $multiSearch->setSearches(array($search1, $search2, $search3));

        $searches = $multiSearch->getSearches();

        $this->assertInternalType('array', $searches);
        $this->assertCount(3, $searches);
        $this->assertArrayHasKey(0, $searches);
        $this->assertSame($search1, $searches[0]);
        $this->assertArrayHasKey(1, $searches);
        $this->assertSame($search2, $searches[1]);
        $this->assertArrayHasKey(2, $searches);
        $this->assertSame($search3, $searches[2]);

        $multiSearch->clearSearches();
        $searches = $multiSearch->getSearches();

        $this->assertInternalType('array', $searches);
        $this->assertCount(0, $searches);
    }

    public function testSearch()
    {
        $type = $this->_createType();
        $index = $type->getIndex();
        $client = $index->getClient();

        $multiSearch = new MultiSearch($client);

        $search1 = new Search($client);
        $search1->addIndex($index)->addType($type);
        $query1 = new Query();
        $termQuery1 = new Term();
        $termQuery1->setTerm('username', 'farrelley');
        $query1->setQuery($termQuery1);
        $query1->setLimit(2);
        $search1->setQuery($query1);

        $multiSearch->addSearch($search1);

        $this->assertCount(1, $multiSearch->getSearches());

        $search2 = new Search($client);
        $search2->addIndex($index)->addType($type);
        $query2 = new Query();
        $termQuery2 = new Term();
        $termQuery2->setTerm('username', 'bunny');
        $query2->setQuery($termQuery2);
        $query2->setLimit(3);
        $search2->setQuery($query2);

        $multiSearch->addSearch($search2);

        $this->assertCount(2, $multiSearch->getSearches());

        $searches = $multiSearch->getSearches();
        $this->assertSame($search1, $searches[0]);
        $this->assertSame($search2, $searches[1]);

        $multiResultSet = $multiSearch->search();

        $this->assertInstanceOf('Elastica\Multi\ResultSet', $multiResultSet);
        $this->assertCount(2, $multiResultSet);
        $this->assertInstanceOf('Elastica\Response', $multiResultSet->getResponse());

        foreach ($multiResultSet as $resultSet) {
            $this->assertInstanceOf('Elastica\ResultSet', $resultSet);
        }

        $resultSets = $multiResultSet->getResultSets();

        $this->assertInternalType('array', $resultSets);

        $this->assertArrayHasKey(0, $resultSets);
        $this->assertInstanceOf('Elastica\ResultSet', $resultSets[0]);
        $this->assertCount(2, $resultSets[0]);
        $this->assertSame($query1, $resultSets[0]->getQuery());
        $this->assertEquals(3, $resultSets[0]->getTotalHits());

        $this->assertArrayHasKey(1, $resultSets);
        $this->assertInstanceOf('Elastica\ResultSet', $resultSets[1]);
        $this->assertCount(3, $resultSets[1]);
        $this->assertSame($query2, $resultSets[1]->getQuery());
        $this->assertEquals(6, $resultSets[1]->getTotalHits());

        $this->assertFalse($multiResultSet->hasError());

        $search1->setOption(Search::OPTION_SEARCH_TYPE, Search::OPTION_SEARCH_TYPE_COUNT);
        $search2->setOption(Search::OPTION_SEARCH_TYPE, Search::OPTION_SEARCH_TYPE_COUNT);

        $multiResultSet = $multiSearch->search();

        $this->assertInstanceOf('Elastica\Multi\ResultSet', $multiResultSet);
        $this->assertCount(2, $multiResultSet);
        $this->assertInstanceOf('Elastica\Response', $multiResultSet->getResponse());

        $resultSets = $multiResultSet->getResultSets();

        $this->assertInternalType('array', $resultSets);

        $this->assertArrayHasKey(0, $resultSets);
        $this->assertInstanceOf('Elastica\ResultSet', $resultSets[0]);
        $this->assertCount(0, $resultSets[0]);
        $this->assertSame($query1, $resultSets[0]->getQuery());
        $this->assertEquals(3, $resultSets[0]->getTotalHits());

        $this->assertArrayHasKey(1, $resultSets);
        $this->assertInstanceOf('Elastica\ResultSet', $resultSets[1]);
        $this->assertCount(0, $resultSets[1]);
        $this->assertSame($query2, $resultSets[1]->getQuery());
        $this->assertEquals(6, $resultSets[1]->getTotalHits());
    }

    public function testSearchWithError()
    {
        $type = $this->_createType();
        $index = $type->getIndex();
        $client = $index->getClient();

        $multiSearch = new MultiSearch($client);

        $searchGood = new Search($client);
        $searchGood->setQuery('bunny');
        $searchGood->addIndex($index)->addType($type);

        $multiSearch->addSearch($searchGood);

        $searchBad = new Search($client);
        $searchBadQuery = new Range();
        $searchBadQuery->addField('bad', array('from' => 0));
        $searchBadQuery->setParam('_cache', true);
        $searchBad->setQuery($searchBadQuery);
        $searchBad->addIndex($index)->addType($type);

        $multiSearch->addSearch($searchBad);

        $multiResultSet = $multiSearch->search();

        $this->assertInstanceOf('Elastica\Multi\ResultSet', $multiResultSet);
        $resultSets = $multiResultSet->getResultSets();
        $this->assertInternalType('array', $resultSets);

        $this->assertArrayHasKey(0, $resultSets);
        $this->assertInstanceOf('Elastica\ResultSet', $resultSets[0]);
        $this->assertSame($searchGood->getQuery(), $resultSets[0]->getQuery());
        $this->assertSame(6, $resultSets[0]->getTotalHits());
        $this->assertCount(6, $resultSets[0]);

        $this->assertArrayHasKey(1, $resultSets);
        $this->assertInstanceOf('Elastica\ResultSet', $resultSets[1]);
        $this->assertSame($searchBad->getQuery(), $resultSets[1]->getQuery());
        $this->assertSame(0, $resultSets[1]->getTotalHits());
        $this->assertCount(0, $resultSets[1]);
        $this->assertTrue($resultSets[1]->getResponse()->hasError());

        $this->assertTrue($multiResultSet->hasError());
    }

    public function testGlobalSearchTypeSearch()
    {
        $type = $this->_createType();
        $index = $type->getIndex();
        $client = $index->getClient();

        $multiSearch = new MultiSearch($client);

        $search1 = new Search($client);
        $search1->addIndex($index)->addType($type);
        $query1 = new Query();
        $termQuery1 = new Term();
        $termQuery1->setTerm('username', 'farrelley');
        $query1->setQuery($termQuery1);
        $query1->setLimit(2);
        $search1->setQuery($query1);

        $multiSearch->addSearch($search1);

        $this->assertCount(1, $multiSearch->getSearches());

        $search2 = new Search($client);
        $search2->addIndex($index)->addType($type);
        $query2 = new Query();
        $termQuery2 = new Term();
        $termQuery2->setTerm('username', 'bunny');
        $query2->setQuery($termQuery2);
        $query2->setLimit(3);
        $search2->setQuery($query2);

        $multiSearch->addSearch($search2);

        $multiSearch->setSearchType(Search::OPTION_SEARCH_TYPE_COUNT);

        $multiResultSet = $multiSearch->search();

        $this->assertInstanceOf('Elastica\Multi\ResultSet', $multiResultSet);
        $this->assertCount(2, $multiResultSet);
        $this->assertInstanceOf('Elastica\Response', $multiResultSet->getResponse());

        $resultSets = $multiResultSet->getResultSets();

        $this->assertInternalType('array', $resultSets);

        $this->assertArrayHasKey(0, $resultSets);
        $this->assertInstanceOf('Elastica\ResultSet', $resultSets[0]);
        $this->assertCount(0, $resultSets[0]);
        $this->assertSame($query1, $resultSets[0]->getQuery());
        $this->assertEquals(3, $resultSets[0]->getTotalHits());

        $this->assertArrayHasKey(1, $resultSets);
        $this->assertInstanceOf('Elastica\ResultSet', $resultSets[1]);
        $this->assertCount(0, $resultSets[1]);
        $this->assertSame($query2, $resultSets[1]->getQuery());
        $this->assertEquals(6, $resultSets[1]->getTotalHits());

        $search1->setOption(Search::OPTION_SEARCH_TYPE, Search::OPTION_SEARCH_TYPE_QUERY_AND_FETCH);

        $multiResultSet = $multiSearch->search();

        $this->assertInstanceOf('Elastica\Multi\ResultSet', $multiResultSet);
        $this->assertCount(2, $multiResultSet);
        $this->assertInstanceOf('Elastica\Response', $multiResultSet->getResponse());

        $resultSets = $multiResultSet->getResultSets();

        $this->assertInternalType('array', $resultSets);

        $this->assertArrayHasKey(0, $resultSets);
        $this->assertInstanceOf('Elastica\ResultSet', $resultSets[0]);
        $this->assertCount(2, $resultSets[0]);
        $this->assertSame($query1, $resultSets[0]->getQuery());
        $this->assertEquals(3, $resultSets[0]->getTotalHits());

        $this->assertArrayHasKey(1, $resultSets);
        $this->assertInstanceOf('Elastica\ResultSet', $resultSets[1]);
        $this->assertCount(0, $resultSets[1]);
        $this->assertSame($query2, $resultSets[1]->getQuery());
        $this->assertEquals(6, $resultSets[1]->getTotalHits());
    }
}
