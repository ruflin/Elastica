<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Response\Elasticsearch;
use Elastica\Aggregation\Cardinality;
use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Query;
use Elastica\Query\FunctionScore;
use Elastica\Query\MatchAll;
use Elastica\Query\QueryString;
use Elastica\Request;
use Elastica\ResultSet;
use Elastica\Script\Script;
use Elastica\Search;
use Elastica\Suggest;
use Elastica\Test\Base as BaseTest;
use GuzzleHttp\Psr7\Response as Psr7Response;
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
        $search = new Search($client);

        $this->assertSame($client, $search->getClient());
    }

    #[Group('functional')]
    public function testAddIndex(): void
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $index1 = $this->_createIndex();
        $index2 = $this->_createIndex();

        $search->addIndex($index1);
        $indices = $search->getIndices();

        $this->assertCount(1, $indices);

        $search->addIndex($index2);
        $indices = $search->getIndices();

        $this->assertCount(2, $indices);

        $this->assertContains($index1->getName(), $indices);
        $this->assertContains($index2->getName(), $indices);
    }

    #[Group('functional')]
    public function testAddIndexByName(): void
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $search->addIndexByName('index1');
        $indices = $search->getIndices();

        $this->assertCount(1, $indices);
        $this->assertContains('index1', $indices);
    }

    #[Group('functional')]
    public function testHasIndex(): void
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $index1 = $this->_createIndex();
        $index2 = $this->_createIndex();

        $this->assertFalse($search->hasIndex($index1));
        $this->assertFalse($search->hasIndex($index2));

        $search->addIndex($index1);
        $search->addIndex($index2);

        $this->assertTrue($search->hasIndex($index1));
        $this->assertTrue($search->hasIndex($index2));
    }

    #[Group('functional')]
    public function testHasIndexByName(): void
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $indexName1 = 'index1';
        $indexName2 = 'index2';

        $this->assertFalse($search->hasIndexByName($indexName1));
        $this->assertFalse($search->hasIndexByName($indexName2));

        $search->addIndexByName($indexName1);
        $search->addIndexByName($indexName2);

        $this->assertTrue($search->hasIndexByName($indexName1));
        $this->assertTrue($search->hasIndexByName($indexName2));
    }

    #[Group('unit')]
    public function testAddIndices(): void
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $indices = [
            $client->getIndex('elastica_test1'),
            $client->getIndex('elastica_test2'),
        ];

        $search->addIndices($indices);
        $this->assertCount(2, $search->getIndices());
    }

    #[Group('unit')]
    public function testAddIndicesWithInvalidParametersThrowsException(): void
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $this->expectException(InvalidException::class);
        $search->addIndices([new \stdClass()]);
    }

    #[Group('unit')]
    public function testAddIndicesByName(): void
    {
        $client = $this->_getClient();
        $search = new Search($client);
        $search->addIndicesByName(['elastica_test1', 'elastica_test2']);

        $this->assertCount(2, $search->getIndices());
    }

    #[Group('unit')]
    public function testAddIndicesByNameWithInvalidParametersThrowsException(): void
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $this->expectException(InvalidException::class);
        $search->addIndicesByName([new \stdClass()]);
    }

    #[Group('functional')]
    public function testGetPath(): void
    {
        $client = $this->_getClient();
        $search1 = new Search($client);

        $index1 = $this->_createIndex();
        $index2 = $this->_createIndex();

        // No index
        $this->assertEquals('/_search', $search1->getPath());

        // Single index
        $search1->addIndex($index1);
        $this->assertEquals($index1->getName().'/_search', $search1->getPath());

        // Multiple indices
        $search1->addIndex($index2);
        $this->assertEquals($index1->getName().','.$index2->getName().'/_search', $search1->getPath());
    }

    #[Group('functional')]
    public function testSearchRequest(): void
    {
        $client = $this->_getClient();
        $search1 = new Search($client);
        $index1 = $this->_createIndex();

        $result = $search1->search([]);
        $this->assertFalse($result->getResponse()->hasError());

        $search1->addIndex($index1);

        $result = $search1->search([]);
        $this->assertFalse($result->getResponse()->hasError());

        $result = $search1->search([]);
        $this->assertFalse($result->getResponse()->hasError());
    }

    #[Group('functional')]
    public function testSearchScrollRequest(): void
    {
        $client = $this->_getClient();

        $index = $this->_createIndex();

        $docs = [];
        for ($x = 1; $x <= 10; ++$x) {
            $docs[] = new Document((string) $x, ['id' => $x, 'testscroll' => 'jbafford']);
        }

        $index->addDocuments($docs);
        $index->refresh();

        $search = new Search($client);
        $search->addIndex($index);
        $search->setOption('size', 0);
        $result = $search->search([], [
            Search::OPTION_SCROLL => '5m',
            Search::OPTION_SIZE => 5,
        ]);
        $this->assertFalse($result->getResponse()->hasError());

        $scrollId = $result->getResponse()->getScrollId();
        $this->assertNotEmpty($scrollId);
        $this->assertCount(5, $result->getResults());

        // There are 10 items, and we're scrolling with a size of 5
        // So we should get two results of 5 items, and then no items
        // We should also have sent the raw scroll_id as the HTTP request body
        $search = new Search($client);
        $result = $search->search([], [
            Search::OPTION_SCROLL => '5m',
            Search::OPTION_SCROLL_ID => $scrollId,
        ]);

        \parse_str($search->getClient()->getLastRequest()->getUri()->getQuery(), $lastRequestQuery);
        $lastRequestData = \json_decode((string) $search->getClient()->getLastRequest()->getBody(), true);

        $this->assertFalse($result->getResponse()->hasError());
        $this->assertCount(5, $result->getResults());
        $this->assertArrayNotHasKey(Search::OPTION_SCROLL_ID, $lastRequestQuery);
        $this->assertEquals([Search::OPTION_SCROLL_ID => $scrollId], $lastRequestData);

        $result = $search->search([], [
            Search::OPTION_SCROLL => '5m',
            Search::OPTION_SCROLL_ID => $scrollId,
        ]);

        \parse_str($search->getClient()->getLastRequest()->getUri()->getQuery(), $lastRequestQuery);
        $lastRequestData = \json_decode((string) $search->getClient()->getLastRequest()->getBody(), true);

        $this->assertFalse($result->getResponse()->hasError());
        $this->assertCount(0, $result->getResults());
        $this->assertArrayNotHasKey(Search::OPTION_SCROLL_ID, $lastRequestQuery);
        $this->assertEquals([Search::OPTION_SCROLL_ID => $scrollId], $lastRequestData);
    }

    /**
     * Default Limit tests for \Elastica\Search.
     */
    #[Group('functional')]
    public function testLimitDefaultSearch(): void
    {
        $client = $this->_getClient();
        $search = new Search($client);

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

        $index->addDocuments([
            new Document('1', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('2', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('3', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('4', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('5', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('6', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('7', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('8', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('9', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('10', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('11', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
        ]);
        $index->refresh();

        $search->addIndex($index);

        // default limit results  (default limit is 10)
        $resultSet = $search->search('farrelley');
        $this->assertEquals(10, $resultSet->count());

        // limit = 1
        $resultSet = $search->search('farrelley', ['limit' => 1]);
        $this->assertEquals(1, $resultSet->count());
    }

    #[Group('functional')]
    public function testArrayConfigSearch(): void
    {
        $client = $this->_getClient();
        $search = new Search($client);

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
        for ($i = 0; $i < 11; ++$i) {
            $docs[] = new Document((string) $i, ['id' => 1, 'email' => 'test@test.com', 'username' => 'test']);
        }

        $index->addDocuments($docs);
        $index->refresh();

        $search->addIndex($index);
        // Backward compatibility, integer => limit
        // default limit results  (default limit is 10)
        $resultSet = $search->search('test');
        $this->assertEquals(10, $resultSet->count());

        // Array with limit
        $resultSet = $search->search('test', ['limit' => 2]);
        $this->assertEquals(2, $resultSet->count());

        // Array with size
        $resultSet = $search->search('test', ['size' => 2]);
        $this->assertEquals(2, $resultSet->count());

        // Array with from
        $resultSet = $search->search('test', ['from' => 10]);
        $this->assertEquals(10, $resultSet->current()->getId());

        // Array with routing
        $resultSet = $search->search('test', ['routing' => 'r1,r2']);
        $this->assertEquals(10, $resultSet->count());

        // Array with limit and routing
        $resultSet = $search->search('test', ['limit' => 5, 'routing' => 'r1,r2']);
        $this->assertEquals(5, $resultSet->count());

        // Array with terminate_after
        $resultSet = $search->search('test', ['terminate_after' => 100]);
        $this->assertEquals(10, $resultSet->count());

        $resultSet = $search->search('test', ['limit' => 0]);
        $this->assertTrue((0 === $resultSet->count()) && 11 === $resultSet->getTotalHits());

        // test with filter_path
        $resultSet = $search->search('test', [Search::OPTION_FILTER_PATH => 'hits.hits._source']);
        $filteredData = $resultSet->getResponse()->getData();
        $this->assertArrayNotHasKey('took', $filteredData);
        $this->assertArrayNotHasKey('max_score', $filteredData['hits']);

        // test with typed_keys
        $countIds = (new Cardinality('count_ids'))->setField('id');
        $suggestName = new Suggest((new Suggest\Term('name_suggest', 'username'))->setText('tes'));
        $typedKeysQuery = (new Query())
            ->addAggregation($countIds)
            ->setSuggest($suggestName)
        ;
        $resultSet = $search->search($typedKeysQuery, [Search::OPTION_TYPED_KEYS => true]);
        $this->assertNotEmpty($resultSet->getAggregation('cardinality#count_ids'));
        $this->assertNotEmpty($resultSet->getSuggests(), 'term#name_suggest');

        // Timeout - this one is a bit more tricky to test
        $mockResponse = new Elasticsearch();
        $mockResponse->setResponse(new Psr7Response(
            200,
            [
                Elasticsearch::HEADER_CHECK => Elasticsearch::PRODUCT_NAME,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            \json_encode(['timed_out' => true])
        ));

        $client = $this->createMock(Client::class);
        $client->method('search')
            ->willReturn($mockResponse)
        ;
        $search = new Search($client);
        $script = new Script('Thread.sleep(100); return _score;');
        $query = new FunctionScore();
        $query->addScriptScoreFunction($script);
        $resultSet = $search->search($query, ['timeout' => 50]);
        $this->assertTrue($resultSet->hasTimedOut());
    }

    #[Group('functional')]
    public function testInvalidConfigSearch(): void
    {
        $this->expectException(InvalidException::class);

        $client = $this->_getClient();
        $search = new Search($client);
        $search->search('test', ['invalid_option' => 'invalid_option_value']);
    }

    #[Group('functional')]
    public function testSearchWithVersionOption(): void
    {
        $index = $this->_createIndex();
        $doc = new Document('1', ['id' => 1, 'email' => 'test@test.com', 'username' => 'ruflin']);
        $index->addDocuments([$doc]);
        $index->refresh();

        $search = new Search($index->getClient());
        $search->addIndex($index);

        // Version param should not be inside by default
        $results = $search->search(new MatchAll());
        $hit = $results->current();
        $this->assertEquals([], $hit->getParam('_version'));

        // Added version param to result
        $results = $search->search(new MatchAll(), ['version' => true]);
        $hit = $results->current();
        $this->assertEquals(1, $hit->getParam('_version'));
    }

    #[Group('functional')]
    public function testSearchGet(): void
    {
        $client = $this->_getClient();
        $search1 = new Search($client);

        $result = $search1->search([], [], 'GET');
        $this->assertFalse($result->getResponse()->hasError());
    }

    #[Group('functional')]
    public function testCountRequest(): void
    {
        $client = $this->_getClient();
        $search = new Search($client);

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

        $index->addDocuments([
            new Document('1', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('2', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('3', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('4', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('5', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('6', ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
            new Document('7', ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
            new Document('8', ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
            new Document('9', ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
            new Document('10', ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
            new Document('11', ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
        ]);
        $index->refresh();

        $search->addIndex($index);

        $count = $search->count('farrelley');
        $this->assertEquals(5, $count);

        $count = $search->count('marley');
        $this->assertEquals(6, $count);

        $count = $search->count();
        $this->assertEquals(6, $count, 'Uses previous query set');

        $count = $search->count(new MatchAll());
        $this->assertEquals(11, $count);

        $count = $search->count('bunny');
        $this->assertEquals(0, $count);
    }

    #[Group('functional')]
    public function testCountRequestGet(): void
    {
        $client = $this->_getClient();
        $search = new Search($client);

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

        $index->addDocuments([
            new Document('1', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('2', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('3', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('4', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('5', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('6', ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
            new Document('7', ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
            new Document('8', ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
            new Document('9', ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
            new Document('10', ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
            new Document('11', ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
        ]);
        $index->refresh();

        $search->addIndex($index);

        $count = $search->count('farrelley', false, Request::GET);
        $this->assertEquals(5, $count);

        $count = $search->count('marley', false, Request::GET);
        $this->assertEquals(6, $count);

        $count = $search->count('', false, Request::GET);
        $this->assertEquals(6, $count, 'Uses previous query set');

        $count = $search->count(new MatchAll(), false, Request::GET);
        $this->assertEquals(11, $count);

        $count = $search->count('bunny', false, Request::GET);
        $this->assertEquals(0, $count);
    }

    #[Group('functional')]
    public function testEmptySearch(): void
    {
        $client = $this->_getClient();
        $search = new Search($client);

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
        $index->addDocuments([
            new Document('1', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('2', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('3', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('4', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('5', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('6', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('7', ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document('8', ['id' => 1, 'email' => 'test@test.com', 'username' => 'bunny']),
            new Document('9', ['id' => 1, 'email' => 'test@test.com', 'username' => 'bunny']),
            new Document('10', ['id' => 1, 'email' => 'test@test.com', 'username' => 'bunny']),
            new Document('11', ['id' => 1, 'email' => 'test@test.com', 'username' => 'bunny']),
        ]);
        $index->refresh();

        $search->addIndex($index);
        $resultSet = $search->search();
        $this->assertInstanceOf(ResultSet::class, $resultSet);
        $this->assertCount(10, $resultSet);
        $this->assertEquals(11, $resultSet->getTotalHits());

        $query = new QueryString('bunny');
        $search->setQuery($query);

        $resultSet = $search->search();

        $this->assertCount(4, $resultSet);
        $this->assertEquals(4, $resultSet->getTotalHits());
        $source = $resultSet->current()->getSource();
        $this->assertEquals('bunny', $source['username']);
    }

    #[Group('functional')]
    public function testCount(): void
    {
        $index = $this->_createIndex();
        $search = new Search($index->getClient());

        $doc = new Document('1', ['id' => 1, 'username' => 'ruflin']);

        $index->addDocuments([$doc]);
        $index->refresh();

        $search->addIndex($index);

        $result1 = $search->count(new MatchAll());
        $this->assertEquals(1, $result1);

        $result2 = $search->count(new MatchAll(), true);
        $this->assertInstanceOf(ResultSet::class, $result2);
        $this->assertEquals(1, $result2->getTotalHits());
    }

    #[Group('functional')]
    public function testIgnoreUnavailableOption(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_7086b4c2ee585bbb6740ece5ed7ece01');
        $query = new MatchAll();

        $search = new Search($client);
        $search->addIndex($index);

        try {
            $search->search($query);
            $this->fail('Should raise an Index not found exception');
        } catch (ClientResponseException $e) {
            $error = \json_decode((string) $e->getResponse()->getBody(), true)['error']['root_cause'][0] ?? null;

            $this->assertEquals('index_not_found_exception', $error['type']);
            $this->assertEquals('no such index [elastica_7086b4c2ee585bbb6740ece5ed7ece01]', $error['reason']);
        }

        $search->search($query, [Search::OPTION_SEARCH_IGNORE_UNAVAILABLE => true]);
    }

    #[Group('functional')]
    public function testQuerySizeAfterCount(): void
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $query = new Query(new MatchAll());
        $query->setSize(25);

        $search->count($query);

        $this->assertEquals(25, $query->getParam('size'));
    }
}
