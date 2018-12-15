<?php

namespace Elastica\Test;

use Elastica\Aggregation\Cardinality;
use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Query;
use Elastica\Query\FunctionScore;
use Elastica\Query\MatchAll;
use Elastica\Query\QueryString;
use Elastica\Response;
use Elastica\ResultSet;
use Elastica\Script\Script;
use Elastica\Search;
use Elastica\Suggest;
use Elastica\Test\Base as BaseTest;

class SearchTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testConstruct()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $this->assertSame($client, $search->getClient());
    }

    /**
     * @group functional
     */
    public function testAddIndex()
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

        // Add string
        $search->addIndex('test3');
        $indices = $search->getIndices();

        $this->assertCount(3, $indices);
        $this->assertContains('test3', $indices);
    }

    /**
     * @group unit
     */
    public function testAddIndices()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $indices = [];
        $indices[] = $client->getIndex('elastica_test1');
        $indices[] = $client->getIndex('elastica_test2');

        $search->addIndices($indices);

        $this->assertCount(2, $search->getIndices());
    }

    /**
     * @group functional
     */
    public function testAddType()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $index = $this->_createIndex();

        $type1 = $index->getType('_doc');
        $type2 = $index->getType('_doc');

        $this->assertEquals([], $search->getTypes());

        $search->addType($type1);
        $types = $search->getTypes();

        $this->assertCount(1, $types);

        $search->addType($type2);
        $types = $search->getTypes();

        $this->assertCount(2, $types);

        $this->assertContains($type1->getName(), $types);
        $this->assertContains($type2->getName(), $types);

        // Add string
        $search->addType('test3');
        $types = $search->getTypes();

        $this->assertCount(3, $types);
        $this->assertContains('test3', $types);
    }

    /**
     * @group unit
     */
    public function testAddTypes()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $index = $client->getIndex('foo');

        $types = [];
        $types[] = $index->getType('_doc');
        $types[] = $index->getType('_doc');

        $search->addTypes($types);

        $this->assertCount(2, $search->getTypes());
    }

    /**
     * @group unit
     */
    public function testAddTypeInvalid()
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $client = $this->_getClient();
        $search = new Search($client);

        $search->addType(new \stdClass());
    }

    /**
     * @group unit
     */
    public function testAddIndexInvalid()
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $client = $this->_getClient();
        $search = new Search($client);

        $search->addIndex(new \stdClass());
    }

    /**
     * @group unit
     */
    public function testAddNumericIndex()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $search->addIndex(1);

        $this->assertContains('1', $search->getIndices(), 'Make sure it has been added and converted to string');
    }

    /**
     * @group functional
     */
    public function testGetPath()
    {
        $client = $this->_getClient();
        $search1 = new Search($client);
        $search2 = new Search($client);

        $index1 = $this->_createIndex();
        $index2 = $this->_createIndex();

        $type1 = $index1->getType('_doc');
        $type2 = $index1->getType('_doc');

        // No index
        $this->assertEquals('/_search', $search1->getPath());

        // Only index
        $search1->addIndex($index1);
        $this->assertEquals($index1->getName().'/_search', $search1->getPath());

        // MUltiple index, no types
        $search1->addIndex($index2);
        $this->assertEquals($index1->getName().','.$index2->getName().'/_search', $search1->getPath());

        // Single type, no index
        $search2->addType($type1);
        $this->assertEquals('_all/'.$type1->getName().'/_search', $search2->getPath());

        // Multiple types
        $search2->addType($type2);
        $this->assertEquals('_all/'.$type1->getName().','.$type2->getName().'/_search', $search2->getPath());

        // Combine index and types
        $search2->addIndex($index1);
        $this->assertEquals($index1->getName().'/'.$type1->getName().','.$type2->getName().'/_search', $search2->getPath());
    }

    /**
     * @group functional
     */
    public function testSearchRequest()
    {
        $client = $this->_getClient();
        $search1 = new Search($client);
        $index1 = $this->_createIndex();
        $type1 = $index1->getType('_doc');

        $result = $search1->search([]);
        $this->assertFalse($result->getResponse()->hasError());

        $search1->addIndex($index1);

        $result = $search1->search([]);
        $this->assertFalse($result->getResponse()->hasError());

        $result = $search1->search([]);
        $this->assertFalse($result->getResponse()->hasError());

        $search1->addType($type1);

        $result = $search1->search([]);
        $this->assertFalse($result->getResponse()->hasError());
    }

    /**
     * @group functional
     */
    public function testSearchScrollRequest()
    {
        $client = $this->_getClient();

        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        $docs = [];
        for ($x = 1; $x <= 10; ++$x) {
            $docs[] = new Document($x, ['id' => $x, 'testscroll' => 'jbafford']);
        }

        $type->addDocuments($docs);
        $index->refresh();

        $search = new Search($client);
        $search->addIndex($index)->addType($type);
        $search->setOption('size', 0);
        $result = $search->search([], [
            Search::OPTION_SCROLL => '5m',
            Search::OPTION_SIZE => 5,
        ]);
        $this->assertFalse($result->getResponse()->hasError());

        $scrollId = $result->getResponse()->getScrollId();
        $this->assertNotEmpty($scrollId);
        $this->assertCount(5, $result->getResults());

        //There are 10 items, and we're scrolling with a size of 5
        //So we should get two results of 5 items, and then no items
        //We should also have sent the raw scroll_id as the HTTP request body
        $search = new Search($client);
        $result = $search->search([], [
            Search::OPTION_SCROLL => '5m',
            Search::OPTION_SCROLL_ID => $scrollId,
        ]);
        $this->assertFalse($result->getResponse()->hasError());
        $this->assertCount(5, $result->getResults());
        $this->assertArrayNotHasKey(Search::OPTION_SCROLL_ID, $search->getClient()->getLastRequest()->getQuery());
        $this->assertEquals([Search::OPTION_SCROLL_ID => $scrollId], $search->getClient()->getLastRequest()->getData());

        $result = $search->search([], [
            Search::OPTION_SCROLL => '5m',
            Search::OPTION_SCROLL_ID => $scrollId,
        ]);
        $this->assertFalse($result->getResponse()->hasError());
        $this->assertCount(0, $result->getResults());
        $this->assertArrayNotHasKey(Search::OPTION_SCROLL_ID, $search->getClient()->getLastRequest()->getQuery());
        $this->assertEquals([Search::OPTION_SCROLL_ID => $scrollId], $search->getClient()->getLastRequest()->getData());
    }

    /**
     * Default Limit tests for \Elastica\Search.
     *
     * @group functional
     */
    public function testLimitDefaultSearch()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $index = $client->getIndex('zero');
        $index->create(['settings' => ['index' => ['number_of_shards' => 1, 'number_of_replicas' => 0]]], true);

        $type = $index->getType('_doc');
        $type->addDocuments([
            new Document(1, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(2, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(3, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(4, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(5, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(6, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(7, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(8, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(9, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(10, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(11, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
        ]);
        $index->refresh();

        $search->addIndex($index)->addType($type);

        // default limit results  (default limit is 10)
        $resultSet = $search->search('farrelley');
        $this->assertEquals(10, $resultSet->count());

        // limit = 1
        $resultSet = $search->search('farrelley', 1);
        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testArrayConfigSearch()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $index = $client->getIndex('zero');
        $index->create(['settings' => ['index' => ['number_of_shards' => 1, 'number_of_replicas' => 0]]], true);

        $docs = [];
        for ($i = 0; $i < 11; ++$i) {
            $docs[] = new Document($i, ['id' => 1, 'email' => 'test@test.com', 'username' => 'test']);
        }

        $type = $index->getType('_doc');
        $type->addDocuments($docs);
        $index->refresh();

        $search->addIndex($index)->addType($type);
        //Backward compatibility, integer => limit
        // default limit results  (default limit is 10)
        $resultSet = $search->search('test');
        $this->assertEquals(10, $resultSet->count());

        // limit = 1
        $resultSet = $search->search('test', 1);
        $this->assertEquals(1, $resultSet->count());

        //Array with limit
        $resultSet = $search->search('test', ['limit' => 2]);
        $this->assertEquals(2, $resultSet->count());

        //Array with size
        $resultSet = $search->search('test', ['size' => 2]);
        $this->assertEquals(2, $resultSet->count());

        //Array with from
        $resultSet = $search->search('test', ['from' => 10]);
        $this->assertEquals(10, $resultSet->current()->getId());

        //Array with routing
        $resultSet = $search->search('test', ['routing' => 'r1,r2']);
        $this->assertEquals(10, $resultSet->count());

        //Array with limit and routing
        $resultSet = $search->search('test', ['limit' => 5, 'routing' => 'r1,r2']);
        $this->assertEquals(5, $resultSet->count());

        //Array with terminate_after
        $resultSet = $search->search('test', ['terminate_after' => 100]);
        $this->assertEquals(10, $resultSet->count());

        $resultSet = $search->search('test', ['limit' => 0]);
        $this->assertTrue((0 === $resultSet->count()) && 11 === $resultSet->getTotalHits());

        //test with filter_path
        $resultSet = $search->search('test', [Search::OPTION_FILTER_PATH => 'hits.hits._source']);
        $filteredData = $resultSet->getResponse()->getData();
        $this->assertArrayNotHasKey('took', $filteredData);
        $this->assertArrayNotHasKey('max_score', $filteredData['hits']);

        //test with typed_keys
        $countIds = (new Cardinality('count_ids'))->setField('id');
        $suggestName = new Suggest((new Suggest\Term('name_suggest', 'username'))->setText('tes'));
        $typedKeysQuery = (new Query())
            ->addAggregation($countIds)
            ->setSuggest($suggestName);
        $resultSet = $search->search($typedKeysQuery, [Search::OPTION_TYPED_KEYS => true]);
        $this->assertNotEmpty($resultSet->getAggregation('cardinality#count_ids'));
        $this->assertNotEmpty($resultSet->getSuggests(), 'term#name_suggest');

        //Timeout - this one is a bit more tricky to test
        $mockResponse = new Response(\json_encode(['timed_out' => true]));
        $client = $this->createMock(Client::class);
        $client->method('request')
            ->will($this->returnValue($mockResponse));
        $search = new Search($client);
        $script = new Script('Thread.sleep(100); return _score;');
        $query = new FunctionScore();
        $query->addScriptScoreFunction($script);
        $resultSet = $search->search($query, ['timeout' => 50]);
        $this->assertTrue($resultSet->hasTimedOut());
    }

    /**
     * @group functional
     */
    public function testInvalidConfigSearch()
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $client = $this->_getClient();
        $search = new Search($client);
        // Throws InvalidException
        $resultSet = $search->search('test', ['invalid_option' => 'invalid_option_value']);
    }

    /**
     * @group functional
     */
    public function testSearchWithVersionOption()
    {
        $index = $this->_createIndex();
        $doc = new Document(1, ['id' => 1, 'email' => 'test@test.com', 'username' => 'ruflin']);
        $index->getType('_doc')->addDocument($doc);
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

    /**
     * @group functional
     */
    public function testCountRequest()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $index = $client->getIndex('zero');
        $index->create(['settings' => ['index' => ['number_of_shards' => 1, 'number_of_replicas' => 0]]], true);

        $type = $index->getType('_doc');
        $type->addDocuments([
            new Document(1, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(2, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(3, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(4, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(5, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(6, ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
            new Document(7, ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
            new Document(8, ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
            new Document(9, ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
            new Document(10, ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
            new Document(11, ['id' => 1, 'email' => 'test@test.com', 'username' => 'marley']),
        ]);
        $index->refresh();

        $search->addIndex($index)->addType($type);

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

    /**
     * @group functional
     */
    public function testEmptySearch()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $index = $client->getIndex('zero');
        $index->create(['settings' => ['index' => ['number_of_shards' => 1, 'number_of_replicas' => 0]]], true);
        $type = $index->getType('_doc');
        $type->addDocuments([
            new Document(1, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(2, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(3, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(4, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(5, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(6, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(7, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']),
            new Document(8, ['id' => 1, 'email' => 'test@test.com', 'username' => 'bunny']),
            new Document(9, ['id' => 1, 'email' => 'test@test.com', 'username' => 'bunny']),
            new Document(10, ['id' => 1, 'email' => 'test@test.com', 'username' => 'bunny']),
            new Document(11, ['id' => 1, 'email' => 'test@test.com', 'username' => 'bunny']),
        ]);
        $index->refresh();

        $search->addIndex($index)->addType($type);
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

    /**
     * @group functional
     */
    public function testCount()
    {
        $index = $this->_createIndex();
        $search = new Search($index->getClient());
        $type = $index->getType('_doc');

        $doc = new Document(1, ['id' => 1, 'username' => 'ruflin']);

        $type->addDocument($doc);
        $index->refresh();

        $search->addIndex($index);
        $search->addType($type);

        $result1 = $search->count(new MatchAll());
        $this->assertEquals(1, $result1);

        $result2 = $search->count(new MatchAll(), true);
        $this->assertInstanceOf(ResultSet::class, $result2);
        $this->assertEquals(1, $result2->getTotalHits());
    }

    /**
     * @group functional
     */
    public function testIgnoreUnavailableOption()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_7086b4c2ee585bbb6740ece5ed7ece01');
        $query = new MatchAll();

        $search = new Search($client);
        $search->addIndex($index);

        $exception = null;
        try {
            $search->search($query);
            $this->fail('Should raise an Index not found exception');
        } catch (ResponseException $e) {
            $error = $e->getResponse()->getFullError();

            $this->assertEquals('index_not_found_exception', $error['type']);
            $this->assertEquals('no such index [elastica_7086b4c2ee585bbb6740ece5ed7ece01]', $error['reason']);
        }

        $results = $search->search($query, [Search::OPTION_SEARCH_IGNORE_UNAVAILABLE => true]);
        $this->assertInstanceOf(ResultSet::class, $results);
    }

    /**
     * @group functional
     */
    public function testQuerySizeAfterCount()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $query = new Query(new MatchAll());
        $query->setSize(25);

        $search->count($query);

        $this->assertEquals(25, $query->getParam('size'));
    }
}
