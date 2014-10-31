<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query\Builder;
use Elastica\Query\MatchAll;
use Elastica\Query\QueryString;
use Elastica\Query\FunctionScore;
use Elastica\Query;
use Elastica\Script;
use Elastica\Search;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;

class SearchTest extends BaseTest
{
    public function testConstruct()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $this->assertInstanceOf('Elastica\Search', $search);
        $this->assertSame($client, $search->getClient());
    }

    public function testAddIndex()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $index1 = $this->_createIndex('test1');
        $index2 = $this->_createIndex('test2');

        $search->addIndex($index1);
        $indices = $search->getIndices();

        $this->assertEquals(1, count($indices));

        $search->addIndex($index2);
        $indices = $search->getIndices();

        $this->assertEquals(2, count($indices));

        $this->assertTrue(in_array($index1->getName(), $indices));
        $this->assertTrue(in_array($index2->getName(), $indices));

        // Add string
        $search->addIndex('test3');
        $indices = $search->getIndices();

        $this->assertEquals(3, count($indices));
        $this->assertTrue(in_array('test3', $indices));
    }

    public function testAddIndices()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $indices = array();
        $indices[] = $client->getIndex('elastica_test1');
        $indices[] = $client->getIndex('elastica_test2');

        $search->addIndices($indices);

        $this->assertEquals(2, count($search->getIndices()));
    }

    public function testAddType()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $index = $this->_createIndex();

        $type1 = $index->getType('type1');
        $type2 = $index->getType('type2');

        $this->assertEquals(array(), $search->getTypes());

        $search->addType($type1);
        $types = $search->getTypes();

        $this->assertEquals(1, count($types));

        $search->addType($type2);
        $types = $search->getTypes();

        $this->assertEquals(2, count($types));

        $this->assertTrue(in_array($type1->getName(), $types));
        $this->assertTrue(in_array($type2->getName(), $types));

        // Add string
        $search->addType('test3');
        $types = $search->getTypes();

        $this->assertEquals(3, count($types));
        $this->assertTrue(in_array('test3', $types));
    }

    public function testAddTypes()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $index = $this->_createIndex();

        $types = array();
        $types[] = $index->getType('type1');
        $types[] = $index->getType('type2');

        $search->addTypes($types);

        $this->assertEquals(2, count($search->getTypes()));
    }

    /**
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testAddTypeInvalid()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $search->addType(new \stdClass());
    }

    /**
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testAddIndexInvalid()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $search->addIndex(new \stdClass());
    }

    public function testGetPath()
    {
        $client = $this->_getClient();
        $search1 = new Search($client);
        $search2 = new Search($client);

        $index1 = $this->_createIndex('test1');
        $index2 = $this->_createIndex('test2');

        $type1 = $index1->getType('type1');
        $type2 = $index1->getType('type2');

        // No index
        $this->assertEquals('/_search', $search1->getPath());

        // Only index
        $search1->addIndex($index1);
        $this->assertEquals($index1->getName() . '/_search', $search1->getPath());

        // MUltiple index, no types
        $search1->addIndex($index2);
        $this->assertEquals($index1->getName() . ',' . $index2->getName() . '/_search', $search1->getPath());

        // Single type, no index
        $search2->addType($type1);
        $this->assertEquals('_all/' . $type1->getName() . '/_search', $search2->getPath());

        // Multiple types
        $search2->addType($type2);
        $this->assertEquals('_all/' . $type1->getName() . ',' . $type2->getName() . '/_search', $search2->getPath());

        // Combine index and types
        $search2->addIndex($index1);
        $this->assertEquals($index1->getName() . '/' . $type1->getName() . ',' . $type2->getName() . '/_search', $search2->getPath());
    }

    public function testSearchRequest()
    {
        $client = $this->_getClient();
        $search1 = new Search($client);

        $index1 = $this->_createIndex('test1');
        $index2 = $this->_createIndex('test2');

        $type1 = $index1->getType('hello1');

        $result = $search1->search(array());
        $this->assertFalse($result->getResponse()->hasError());

        $search1->addIndex($index1);

        $result = $search1->search(array());
        $this->assertFalse($result->getResponse()->hasError());

        $search1->addIndex($index2);

        $result = $search1->search(array());
        $this->assertFalse($result->getResponse()->hasError());

        $search1->addType($type1);

        $result = $search1->search(array());
        $this->assertFalse($result->getResponse()->hasError());
    }

    public function testSearchScrollRequest()
    {
        $client = $this->_getClient();

        $index = $this->_createIndex('test');
        $type = $index->getType('scrolltest');

        $docs = array();
        for ($x = 1; $x <= 10; $x++) {
            $docs[] = new Document($x, array('id' => $x, 'testscroll' => 'jbafford'));
        }

        $type->addDocuments($docs);
        $index->refresh();

        $search = new Search($client);
        $search->addIndex($index)->addType($type);
        $result = $search->search(array(), array(
            Search::OPTION_SEARCH_TYPE => Search::OPTION_SEARCH_TYPE_SCAN,
            Search::OPTION_SCROLL => '5m',
            Search::OPTION_SIZE => 5,
        ));
        $this->assertFalse($result->getResponse()->hasError());

        $scrollId = $result->getResponse()->getScrollId();
        $this->assertNotEmpty($scrollId);

        //There are 10 items, and we're scrolling with a size of 5
        //So we should get two results of 5 items, and then no items
        //We should also have sent the raw scroll_id as the HTTP request body
        $search = new Search($client);
        $result = $search->search(array(), array(
            Search::OPTION_SCROLL => '5m',
            Search::OPTION_SCROLL_ID => $scrollId,
        ));
        $this->assertFalse($result->getResponse()->hasError());
        $this->assertEquals(5, count($result->getResults()));
        $this->assertArrayNotHasKey(Search::OPTION_SCROLL_ID, $search->getClient()->getLastRequest()->getQuery());
        $this->assertEquals($scrollId, $search->getClient()->getLastRequest()->getData());

        $result = $search->search(array(), array(
            Search::OPTION_SCROLL => '5m',
            Search::OPTION_SCROLL_ID => $scrollId,
        ));
        $this->assertFalse($result->getResponse()->hasError());
        $this->assertEquals(5, count($result->getResults()));
        $this->assertArrayNotHasKey(Search::OPTION_SCROLL_ID, $search->getClient()->getLastRequest()->getQuery());
        $this->assertEquals($scrollId, $search->getClient()->getLastRequest()->getData());

        $result = $search->search(array(), array(
            Search::OPTION_SCROLL => '5m',
            Search::OPTION_SCROLL_ID => $scrollId,
        ));
        $this->assertFalse($result->getResponse()->hasError());
        $this->assertEquals(0, count($result->getResults()));
        $this->assertArrayNotHasKey(Search::OPTION_SCROLL_ID, $search->getClient()->getLastRequest()->getQuery());
        $this->assertEquals($scrollId, $search->getClient()->getLastRequest()->getData());
    }

    /**
     * Default Limit tests for \Elastica\Search
     */
    public function testLimitDefaultSearch()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $index = $client->getIndex('zero');
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);

        $docs = array();
        $docs[] = new Document(1, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(2, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(3, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(4, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(5, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(6, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(7, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(8, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(9, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(10, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(11, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $type = $index->getType('zeroType');
        $type->addDocuments($docs);
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
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testArrayConfigSearch()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $index = $client->getIndex('zero');
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);

        $docs = array();
        for ($i = 0; $i < 11; $i++) {
            $docs[] = new Document($i, array('id' => 1, 'email' => 'test@test.com', 'username' => 'test'));
        }

        $type = $index->getType('zeroType');
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
        $resultSet = $search->search('test', array('limit' => 2));
        $this->assertEquals(2, $resultSet->count());

        //Array with size
        $resultSet = $search->search('test', array('size' => 2));
        $this->assertEquals(2, $resultSet->count());

        //Array with from
        $resultSet = $search->search('test', array('from' => 10));
        $this->assertEquals(10, $resultSet->current()->getId());

        //Array with routing
        $resultSet = $search->search('test', array('routing' => 'r1,r2'));
        $this->assertEquals(10, $resultSet->count());

        //Array with limit and routing
        $resultSet = $search->search('test', array('limit' => 5, 'routing' => 'r1,r2'));
        $this->assertEquals(5, $resultSet->count());

        //Search types
        $resultSet = $search->search('test', array('limit' => 5, 'search_type' => 'count'));
        $this->assertTrue(($resultSet->count() === 0) && $resultSet->getTotalHits() === 11);

        //Timeout - this one is a bit more tricky to test
        $script = new Script('Thread.sleep(100); return _score;');
        $query = new FunctionScore();
        $query->addScriptScoreFunction($script);
        $resultSet = $search->search($query, array('timeout' => 50));
        $this->assertTrue($resultSet->hasTimedOut());

        // Throws InvalidException
        $resultSet = $search->search('test', array('invalid_option' => 'invalid_option_value'));
    }

    public function testSearchWithVersionOption()
    {
        $index = $this->_createIndex('test1');
        $doc = new Document(1, array('id' => 1, 'email' => 'test@test.com', 'username' => 'ruflin'));
        $index->getType('test')->addDocument($doc);
        $index->refresh();

        $search = new Search($index->getClient());
        $search->addIndex($index);

        // Version param should not be inside by default
        $results = $search->search(new MatchAll());
        $hit = $results->current();
        $this->assertEquals(array(), $hit->getParam('_version'));

        // Added version param to result
        $results = $search->search(new MatchAll(), array('version' => true));
        $hit = $results->current();
        $this->assertEquals(1, $hit->getParam('_version'));
    }

    public function testCountRequest()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $index = $client->getIndex('zero');
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);

        $docs = array();
        $docs[] = new Document(1,  array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(2,  array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(3,  array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(4,  array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(5,  array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(6,  array('id' => 1, 'email' => 'test@test.com', 'username' => 'marley'));
        $docs[] = new Document(7,  array('id' => 1, 'email' => 'test@test.com', 'username' => 'marley'));
        $docs[] = new Document(8,  array('id' => 1, 'email' => 'test@test.com', 'username' => 'marley'));
        $docs[] = new Document(9,  array('id' => 1, 'email' => 'test@test.com', 'username' => 'marley'));
        $docs[] = new Document(10, array('id' => 1, 'email' => 'test@test.com', 'username' => 'marley'));
        $docs[] = new Document(11, array('id' => 1, 'email' => 'test@test.com', 'username' => 'marley'));

        $type = $index->getType('zeroType');
        $type->addDocuments($docs);
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

    public function testEmptySearch()
    {
        $client = $this->_getClient();
        $search = new Search($client);

        $index = $client->getIndex('zero');
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);
        $docs = array();
        $docs[] = new Document(1, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(2, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(3, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(4, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(5, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(6, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(7, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Document(8, array('id' => 1, 'email' => 'test@test.com', 'username' => 'bunny'));
        $docs[] = new Document(9, array('id' => 1, 'email' => 'test@test.com', 'username' => 'bunny'));
        $docs[] = new Document(10, array('id' => 1, 'email' => 'test@test.com', 'username' => 'bunny'));
        $docs[] = new Document(11, array('id' => 1, 'email' => 'test@test.com', 'username' => 'bunny'));
        $type = $index->getType('zeroType');
        $type->addDocuments($docs);
        $index->refresh();

        $search->addIndex($index)->addType($type);
        $resultSet = $search->search();
        $this->assertInstanceOf('Elastica\ResultSet', $resultSet);
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
	
	public function testCount() {
        $index = $this->_createIndex('eeee');
        $search = new Search($index->getClient());
		$type = $index->getType('test');
		
        $doc = new Document(1, array('id' => 1, 'username' => 'ruflin'));
		
		$type->addDocument($doc);
		$index->refresh();
		
		$search->addIndex($index);
		$search->addType($type);
		
		$result1 = $search->count(new \Elastica\Query\MatchAll());
		$this->assertEquals(1, $result1);
		
		
		$result2 = $search->count(new \Elastica\Query\MatchAll(), true);
		$this->assertInstanceOf('\Elastica\ResultSet', $result2);
		$this->assertEquals(1, $result2->getTotalHits());
	}

    public function testScanAndScroll() {
        $search = new Search($this->_getClient());
        $this->assertInstanceOf('Elastica\ScanAndScroll', $search->scanAndScroll());
    }
}
