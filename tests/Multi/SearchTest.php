<?php

declare(strict_types=1);

namespace Elastica\Test\Multi;

use Elastica\Document;
use Elastica\Index;
use Elastica\Multi\Search as MultiSearch;
use Elastica\Query;
use Elastica\Query\Term;
use Elastica\ResultSet;
use Elastica\Search;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class SearchTest extends BaseTest
{
    #[Group('unit')]
    public function testConstruct(): void
    {
        $client = $this->_getClient();
        $multiSearch = new MultiSearch($client);

        $this->assertSame($client, $multiSearch->getClient());
    }

    #[Group('unit')]
    public function testSetSearches(): void
    {
        $client = $this->_getClient();
        $multiSearch = new MultiSearch($client);

        $search1 = new Search($client);
        $search2 = new Search($client);
        $search3 = new Search($client);

        $multiSearch->setSearches([$search1, $search2, $search3]);

        $searches = $multiSearch->getSearches();

        $this->assertIsArray($searches);
        $this->assertCount(3, $searches);
        $this->assertArrayHasKey(0, $searches);
        $this->assertSame($search1, $searches[0]);
        $this->assertArrayHasKey(1, $searches);
        $this->assertSame($search2, $searches[1]);
        $this->assertArrayHasKey(2, $searches);
        $this->assertSame($search3, $searches[2]);

        $multiSearch->clearSearches();
        $searches = $multiSearch->getSearches();

        $this->assertIsArray($searches);
        $this->assertCount(0, $searches);
    }

    #[Group('unit')]
    public function testSetSearchesByKeys(): void
    {
        $client = $this->_getClient();
        $multiSearch = new MultiSearch($client);

        $search1 = new Search($client);
        $search2 = new Search($client);
        $search3 = new Search($client);

        $multiSearch->setSearches(['search1' => $search1, 'search2' => $search2, $search3]);

        $searches = $multiSearch->getSearches();

        $this->assertIsArray($searches);
        $this->assertCount(3, $searches);
        $this->assertArrayHasKey('search1', $searches);
        $this->assertSame($search1, $searches['search1']);
        $this->assertArrayHasKey('search2', $searches);
        $this->assertSame($search2, $searches['search2']);
        $this->assertArrayHasKey(0, $searches);
        $this->assertSame($search3, $searches[0]);

        $multiSearch->clearSearches();
        $searches = $multiSearch->getSearches();

        $this->assertIsArray($searches);
        $this->assertCount(0, $searches);
    }

    #[Group('functional')]
    public function testSearch(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $multiSearch = new MultiSearch($client);

        $search1 = new Search($client);
        $search1->addIndex($index);
        $query1 = new Query();
        $termQuery1 = new Term();
        $termQuery1->setTerm('username', 'farrelley');
        $query1->setQuery($termQuery1);
        $query1->setSize(2);
        $search1->setQuery($query1);

        $multiSearch->addSearch($search1);

        $this->assertCount(1, $multiSearch->getSearches());

        $search2 = new Search($client);
        $search2->addIndex($index);
        $query2 = new Query();
        $termQuery2 = new Term();
        $termQuery2->setTerm('username', 'bunny');
        $query2->setQuery($termQuery2);
        $query2->setSize(3);
        $search2->setQuery($query2);

        $multiSearch->addSearch($search2);

        $this->assertCount(2, $multiSearch->getSearches());

        $searches = $multiSearch->getSearches();
        $this->assertSame($search1, $searches[0]);
        $this->assertSame($search2, $searches[1]);

        $multiResultSet = $multiSearch->search();

        $this->assertCount(2, $multiResultSet);
        $this->assertContainsOnlyInstancesOf(ResultSet::class, $multiResultSet);

        $resultSets = $multiResultSet->getResultSets();

        $this->assertIsArray($resultSets);

        $this->assertArrayHasKey(0, $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets[0]);
        $this->assertCount(2, $resultSets[0]);
        $this->assertSame($query1, $resultSets[0]->getQuery());
        $this->assertEquals(3, $resultSets[0]->getTotalHits());

        $this->assertArrayHasKey(1, $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets[1]);
        $this->assertCount(3, $resultSets[1]);
        $this->assertSame($query2, $resultSets[1]->getQuery());
        $this->assertEquals(6, $resultSets[1]->getTotalHits());

        $this->assertFalse($multiResultSet->hasError());

        $search1->getQuery()->setSize(0);
        $search2->getQuery()->setSize(0);
        $multiResultSet = $multiSearch->search();

        $this->assertCount(2, $multiResultSet);

        $resultSets = $multiResultSet->getResultSets();

        $this->assertIsArray($resultSets);

        $this->assertArrayHasKey(0, $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets[0]);
        $this->assertCount(0, $resultSets[0]);
        $this->assertSame($query1, $resultSets[0]->getQuery());
        $this->assertEquals(3, $resultSets[0]->getTotalHits());

        $this->assertArrayHasKey(1, $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets[1]);
        $this->assertCount(0, $resultSets[1]);
        $this->assertSame($query2, $resultSets[1]->getQuery());
        $this->assertEquals(6, $resultSets[1]->getTotalHits());
    }

    #[Group('functional')]
    public function testSearchWithKeys(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $multiSearch = new MultiSearch($client);

        $search1 = new Search($client);
        $search1->addIndex($index);
        $query1 = new Query();
        $termQuery1 = new Term();
        $termQuery1->setTerm('username', 'farrelley');
        $query1->setQuery($termQuery1);
        $query1->setSize(2);
        $search1->setQuery($query1);

        $multiSearch->addSearch($search1, 'search1');

        $this->assertCount(1, $multiSearch->getSearches());

        $search2 = new Search($client);
        $search2->addIndex($index);
        $query2 = new Query();
        $termQuery2 = new Term();
        $termQuery2->setTerm('username', 'bunny');
        $query2->setQuery($termQuery2);
        $query2->setSize(3);
        $search2->setQuery($query2);

        $multiSearch->addSearch($search2, 'search2');

        $this->assertCount(2, $multiSearch->getSearches());

        $searches = $multiSearch->getSearches();
        $this->assertSame($search1, $searches['search1']);
        $this->assertSame($search2, $searches['search2']);

        $multiResultSet = $multiSearch->search();

        $this->assertCount(2, $multiResultSet);
        $this->assertContainsOnlyInstancesOf(ResultSet::class, $multiResultSet);
        $this->assertInstanceOf(ResultSet::class, $multiResultSet['search1']);
        $this->assertInstanceOf(ResultSet::class, $multiResultSet['search2']);

        $resultSets = $multiResultSet->getResultSets();

        $this->assertIsArray($resultSets);

        $this->assertArrayHasKey('search1', $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets['search1']);
        $this->assertCount(2, $resultSets['search1']);
        $this->assertSame($query1, $resultSets['search1']->getQuery());
        $this->assertEquals(3, $resultSets['search1']->getTotalHits());

        $this->assertArrayHasKey('search2', $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets['search2']);
        $this->assertCount(3, $resultSets['search2']);
        $this->assertSame($query2, $resultSets['search2']->getQuery());
        $this->assertEquals(6, $resultSets['search2']->getTotalHits());

        $this->assertFalse($multiResultSet->hasError());

        $search1->getQuery()->setSize(0);
        $search2->getQuery()->setSize(0);
        $multiResultSet = $multiSearch->search();

        $this->assertCount(2, $multiResultSet);

        $resultSets = $multiResultSet->getResultSets();

        $this->assertIsArray($resultSets);

        $this->assertArrayHasKey('search1', $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets['search1']);
        $this->assertCount(0, $resultSets['search1']);
        $this->assertSame($query1, $resultSets['search1']->getQuery());
        $this->assertEquals(3, $resultSets['search1']->getTotalHits());

        $this->assertArrayHasKey('search2', $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets['search2']);
        $this->assertCount(0, $resultSets['search2']);
        $this->assertSame($query2, $resultSets['search2']->getQuery());
        $this->assertEquals(6, $resultSets['search2']->getTotalHits());
    }

    #[Group('functional')]
    public function testSearchWithError(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $multiSearch = new MultiSearch($client);

        $searchGood = new Search($client);
        $searchGood->setQuery('bunny');
        $searchGood->addIndex($index);

        $multiSearch->addSearch($searchGood);

        $searchBad = new Search($client);
        $searchBad->setOption(Search::OPTION_SIZE, -2);
        $searchBad->addIndex($index);

        $multiSearch->addSearch($searchBad);

        $this->markTestSkipped('Elastic\Elasticsearch\Exception\ClientResponseException: [size] parameter cannot be negative, found [-2]');
        $multiResultSet = $multiSearch->search();
        $resultSets = $multiResultSet->getResultSets();
        $this->assertIsArray($resultSets);

        $this->assertArrayHasKey(0, $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets[0]);
        $this->assertSame($searchGood->getQuery(), $resultSets[0]->getQuery());
        $this->assertSame(6, $resultSets[0]->getTotalHits());
        $this->assertCount(6, $resultSets[0]);

        $this->assertArrayHasKey(1, $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets[1]);
        $this->assertSame($searchBad->getQuery(), $resultSets[1]->getQuery());

        $this->assertSame(0, $resultSets[1]->getTotalHits());
        $this->assertCount(0, $resultSets[1]);

        $this->assertTrue($resultSets[1]->getResponse()->hasError());

        $this->assertTrue($multiResultSet->hasError());
    }

    #[Group('functional')]
    public function testSearchWithErrorWithKeys(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $multiSearch = new MultiSearch($client);

        $searchGood = new Search($client);
        $searchGood->setQuery('bunny');
        $searchGood->addIndex($index);

        $multiSearch->addSearch($searchGood, 'search1');

        $searchBad = new Search($client);
        $searchBad->setOption(Search::OPTION_SIZE, -2);
        $searchBad->addIndex($index);

        $multiSearch->addSearch($searchBad);

        $this->markTestSkipped('Elastic\Elasticsearch\Exception\ClientResponseException: [size] parameter cannot be negative, found [-2]');
        $multiResultSet = $multiSearch->search();
        $resultSets = $multiResultSet->getResultSets();
        $this->assertIsArray($resultSets);

        $this->assertArrayHasKey('search1', $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets['search1']);
        $this->assertSame($searchGood->getQuery(), $resultSets['search1']->getQuery());
        $this->assertSame(6, $resultSets['search1']->getTotalHits());
        $this->assertCount(6, $resultSets['search1']);

        $this->assertArrayHasKey(0, $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets[0]);
        $this->assertSame($searchBad->getQuery(), $resultSets[0]->getQuery());

        $this->assertSame(0, $resultSets[0]->getTotalHits());
        $this->assertCount(0, $resultSets[0]);

        $this->assertTrue($resultSets[0]->getResponse()->hasError());

        $this->assertTrue($multiResultSet->hasError());
    }

    #[Group('functional')]
    public function testGlobalSearchTypeSearch(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $multiSearch = new MultiSearch($client);

        $search1 = new Search($client);
        $search1->addIndex($index);
        $query1 = new Query();
        $termQuery1 = new Term();
        $termQuery1->setTerm('username', 'farrelley');
        $query1->setQuery($termQuery1);
        $query1->setSize(2);
        $search1->setQuery($query1);

        $multiSearch->addSearch($search1);

        $this->assertCount(1, $multiSearch->getSearches());

        $search2 = new Search($client);
        $search2->addIndex($index);
        $query2 = new Query();
        $termQuery2 = new Term();
        $termQuery2->setTerm('username', 'bunny');
        $query2->setQuery($termQuery2);
        $query2->setSize(3);
        $search2->setQuery($query2);

        $multiSearch->addSearch($search2);

        $search1->getQuery()->setSize(0);
        $search2->getQuery()->setSize(0);
        $multiResultSet = $multiSearch->search();

        $this->assertCount(2, $multiResultSet);

        $resultSets = $multiResultSet->getResultSets();

        $this->assertIsArray($resultSets);

        $this->assertArrayHasKey(0, $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets[0]);
        $this->assertCount(0, $resultSets[0]);
        $this->assertSame($query1, $resultSets[0]->getQuery());
        $this->assertEquals(3, $resultSets[0]->getTotalHits());

        $this->assertArrayHasKey(1, $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets[1]);
        $this->assertCount(0, $resultSets[1]);
        $this->assertSame($query2, $resultSets[1]->getQuery());
        $this->assertEquals(6, $resultSets[1]->getTotalHits());

        $search1->setOption(Search::OPTION_SEARCH_TYPE, Search::OPTION_SEARCH_TYPE_DFS_QUERY_THEN_FETCH);

        $multiResultSet = $multiSearch->search();

        $this->assertCount(2, $multiResultSet);

        $resultSets = $multiResultSet->getResultSets();

        $this->assertIsArray($resultSets);

        $this->assertArrayHasKey(0, $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets[0]);
        $this->assertCount(0, $resultSets[0]);
        $this->assertSame($query1, $resultSets[0]->getQuery());
        $this->assertEquals(3, $resultSets[0]->getTotalHits());

        $this->assertArrayHasKey(1, $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets[1]);
        $this->assertCount(0, $resultSets[1]);
        $this->assertSame($query2, $resultSets[1]->getQuery());
        $this->assertEquals(6, $resultSets[1]->getTotalHits());
    }

    #[Group('functional')]
    public function testGlobalSearchTypeSearchWithKeys(): void
    {
        $index = $this->_createIndex();
        $client = $index->getClient();

        $multiSearch = new MultiSearch($client);

        $search1 = new Search($client);
        $search1->addIndex($index);
        $query1 = new Query();
        $termQuery1 = new Term();
        $termQuery1->setTerm('username', 'farrelley');
        $query1->setQuery($termQuery1);
        $query1->setSize(2);
        $search1->setQuery($query1);

        $multiSearch->addSearch($search1);

        $this->assertCount(1, $multiSearch->getSearches());

        $search2 = new Search($client);
        $search2->addIndex($index);
        $query2 = new Query();
        $termQuery2 = new Term();
        $termQuery2->setTerm('username', 'bunny');
        $query2->setQuery($termQuery2);
        $query2->setSize(3);
        $search2->setQuery($query2);

        $multiSearch->addSearch($search2);

        $search1->getQuery()->setSize(0);
        $search2->getQuery()->setSize(0);

        $multiResultSet = $multiSearch->search();

        $this->assertCount(2, $multiResultSet);

        $resultSets = $multiResultSet->getResultSets();

        $this->assertIsArray($resultSets);

        $this->assertArrayHasKey(0, $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets[0]);
        $this->assertCount(0, $resultSets[0]);
        $this->assertSame($query1, $resultSets[0]->getQuery());
        $this->assertEquals(3, $resultSets[0]->getTotalHits());

        $this->assertArrayHasKey(1, $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets[1]);
        $this->assertCount(0, $resultSets[1]);
        $this->assertSame($query2, $resultSets[1]->getQuery());
        $this->assertEquals(6, $resultSets[1]->getTotalHits());

        $search1->setOption(Search::OPTION_SEARCH_TYPE, Search::OPTION_SEARCH_TYPE_DFS_QUERY_THEN_FETCH);

        $multiResultSet = $multiSearch->search();

        $this->assertCount(2, $multiResultSet);

        $resultSets = $multiResultSet->getResultSets();

        $this->assertIsArray($resultSets);

        $this->assertArrayHasKey(0, $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets[0]);
        $this->assertCount(0, $resultSets[0]);
        $this->assertSame($query1, $resultSets[0]->getQuery());
        $this->assertEquals(3, $resultSets[0]->getTotalHits());

        $this->assertArrayHasKey(1, $resultSets);
        $this->assertInstanceOf(ResultSet::class, $resultSets[1]);
        $this->assertCount(0, $resultSets[1]);
        $this->assertSame($query2, $resultSets[1]->getQuery());
        $this->assertEquals(6, $resultSets[1]->getTotalHits());
    }

    protected function _createIndex(?string $name = null, bool $delete = true, int $shards = 1): Index
    {
        $client = $this->_getClient();

        $index = $client->getIndex('zero');
        $index->create(
            [
                'settings' => [
                    'index' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 0,
                    ],
                ],
            ],
            [
                'recreate' => true,
            ]
        );

        $docs = [];
        $docs[] = new Document('1', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']);
        $docs[] = new Document('2', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']);
        $docs[] = new Document('3', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']);
        $docs[] = new Document('4', ['id' => 1, 'email' => 'test@test.com', 'username' => 'kate']);
        $docs[] = new Document('5', ['id' => 1, 'email' => 'test@test.com', 'username' => 'kate']);
        $docs[] = new Document('6', ['id' => 1, 'email' => 'test@test.com', 'username' => 'bunny']);
        $docs[] = new Document('7', ['id' => 1, 'email' => 'test@test.com', 'username' => 'bunny']);
        $docs[] = new Document('8', ['id' => 1, 'email' => 'test@test.com', 'username' => 'bunny']);
        $docs[] = new Document('9', ['id' => 1, 'email' => 'test@test.com', 'username' => 'bunny']);
        $docs[] = new Document('10', ['id' => 1, 'email' => 'test@test.com', 'username' => 'bunny']);
        $docs[] = new Document('11', ['id' => 1, 'email' => 'test@test.com', 'username' => 'bunny']);
        $index->addDocuments($docs);
        $index->refresh();

        return $index;
    }
}
