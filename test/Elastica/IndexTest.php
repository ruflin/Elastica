<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Query\QueryString;
use Elastica\Query\SimpleQueryString;
use Elastica\Query\Term;
use Elastica\Request;
use Elastica\Script\Script;
use Elastica\Status;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;
use Elastica\Type\Mapping;
use Elasticsearch\Endpoints\Indices\Analyze;

class IndexTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testMapping()
    {
        $index = $this->_createIndex();
        $doc = new Document(1, ['id' => 1, 'email' => 'test@test.com', 'username' => 'hanswurst', 'test' => ['2', '3', '5']]);

        $type = $index->getType('_doc');

        $mapping = ['id' => ['type' => 'integer', 'store' => true], 'email' => ['type' => 'text'],
            'username' => ['type' => 'text'], 'test' => ['type' => 'integer'], ];
        $type->setMapping($mapping);

        $type->addDocument($doc);
        $index->forcemerge();

        $storedMapping = $index->getMapping();

        $this->assertEquals($storedMapping['properties']['id']['type'], 'integer');
        $this->assertEquals($storedMapping['properties']['id']['store'], true);
        $this->assertEquals($storedMapping['properties']['email']['type'], 'text');
        $this->assertEquals($storedMapping['properties']['username']['type'], 'text');
        $this->assertEquals($storedMapping['properties']['test']['type'], 'integer');

        $result = $type->search('hanswurst');
    }

    /**
     * @group functional
     */
    public function testGetMappingAlias()
    {
        $index = $this->_createIndex();
        $indexName = $index->getName();

        $aliasName = 'test-mapping-alias';
        $index->addAlias($aliasName);

        $type = new Type($index, '_doc');
        $mapping = new Mapping($type, [
                'id' => ['type' => 'integer', 'store' => 'true'],
            ]);
        $type->setMapping($mapping);

        $client = $index->getClient();

        // Index mapping
        $mapping1 = $client->getIndex($indexName)->getMapping();

        // Alias mapping
        $mapping2 = $client->getIndex($aliasName)->getMapping();

        // Make sure, a mapping is set
        $this->assertNotEmpty($mapping1);

        // Alias and index mapping should be identical
        $this->assertEquals($mapping1, $mapping2);
    }

    /**
     * @group functional
     */
    public function testAddRemoveAlias()
    {
        $this->expectException(\Elastica\Exception\ResponseException::class);

        $client = $this->_getClient();

        $indexName1 = 'test1';
        $aliasName = 'test-alias';
        $typeName = 'test';

        $index = $client->getIndex($indexName1);
        $index->create(['settings' => ['index' => ['number_of_shards' => 1, 'number_of_replicas' => 0]]], true);

        $doc = new Document(1, ['id' => 1, 'email' => 'test@test.com', 'username' => 'ruflin']);

        $type = $index->getType($typeName);
        $type->addDocument($doc);
        $index->refresh();

        $resultSet = $type->search('ruflin');

        $this->assertEquals(1, $resultSet->count());

        $data = $index->addAlias($aliasName, true)->getData();
        $this->assertTrue($data['acknowledged']);

        $response = $index->removeAlias($aliasName)->getData();
        $this->assertTrue($response['acknowledged']);

        $client->getIndex($aliasName)->getType($typeName)->search('ruflin');
    }

    /**
     * @group functional
     */
    public function testCount()
    {
        $index = $this->_createIndex();

        // Add document to normal index
        $doc1 = new Document(null, ['name' => 'ruflin']);
        $doc2 = new Document(null, ['name' => 'nicolas']);

        $type = $index->getType('_doc');
        $type->addDocument($doc1);
        $type->addDocument($doc2);

        $index->refresh();

        $this->assertEquals(2, $index->count());

        $query = new Term();
        $key = 'name';
        $value = 'nicolas';
        $query->setTerm($key, $value);

        $this->assertEquals(1, $index->count($query));
    }

    /**
     * @group functional
     */
    public function testDeleteByQueryWithQueryString()
    {
        $index = $this->_createIndex();
        $type1 = new Type($index, '_doc');
        $type1->addDocument(new Document(1, ['name' => 'ruflin nicolas']));
        $type1->addDocument(new Document(2, ['name' => 'ruflin']));
        $index->refresh();

        $response = $index->search('ruflin*');
        $this->assertEquals(2, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(1, $response->count());

        // Delete first document
        $response = $index->deleteByQuery('nicolas');
        $this->assertTrue($response->isOk());

        $index->refresh();

        // Makes sure, document is deleted
        $response = $index->search('ruflin*');
        $this->assertEquals(1, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(0, $response->count());
    }

    /**
     * @group functional
     */
    public function testDeleteByQueryWithQuery()
    {
        $index = $this->_createIndex();
        $type1 = new Type($index, '_doc');
        $type1->addDocument(new Document(1, ['name' => 'ruflin nicolas']));
        $type1->addDocument(new Document(2, ['name' => 'ruflin']));
        $index->refresh();

        $response = $index->search('ruflin*');
        $this->assertEquals(2, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(1, $response->count());

        // Delete first document
        $response = $index->deleteByQuery(new SimpleQueryString('nicolas'));
        $this->assertTrue($response->isOk());

        $index->refresh();

        // Makes sure, document is deleted
        $response = $index->search('ruflin*');
        $this->assertEquals(1, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(0, $response->count());
    }

    /**
     * @group functional
     */
    public function testDeleteByQueryWithArrayQuery()
    {
        $index = $this->_createIndex();
        $type1 = new Type($index, '_doc');
        $type1->addDocument(new Document(1, ['name' => 'ruflin nicolas']));
        $type1->addDocument(new Document(2, ['name' => 'ruflin']));
        $index->refresh();

        $response = $index->search('ruflin*');
        $this->assertEquals(2, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(1, $response->count());

        // Delete first document
        $response = $index->deleteByQuery(['query' => ['query_string' => ['query' => 'nicolas']]]);
        $this->assertTrue($response->isOk());

        $index->refresh();

        // Makes sure, document is deleted
        $response = $index->search('ruflin*');
        $this->assertEquals(1, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(0, $response->count());
    }

    /**
     * @group functional
     */
    public function testDeleteByQueryWithQueryAndOptions()
    {
        $index = $this->_createIndex(null, true, 2);

        $routing1 = 'first_routing';
        $routing2 = 'second_routing';

        $type = new Type($index, '_doc');
        $doc = new Document(1, ['name' => 'ruflin nicolas']);
        $doc->setRouting($routing1);
        $type->addDocument($doc);

        $doc = new Document(2, ['name' => 'ruflin']);
        $doc->setRouting($routing1);
        $type->addDocument($doc);

        $doc = new Document(2, ['name' => 'ruflin']);
        $doc->setRouting($routing1);
        $type->addDocument($doc);

        $index->refresh();

        $response = $index->search('ruflin*');
        $this->assertEquals(2, $response->count());

        $response = $index->search('ruflin*', ['routing' => $routing2]);
        $this->assertEquals(0, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(1, $response->count());

        // Route to the wrong document id; should not delete
        $response = $index->deleteByQuery(new SimpleQueryString('nicolas'), ['routing' => $routing2]);
        $this->assertTrue($response->isOk());

        $index->refresh();

        $response = $index->search('ruflin*');
        $this->assertEquals(2, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(1, $response->count());

        // Delete first document
        $response = $index->deleteByQuery(new SimpleQueryString('nicolas'), ['routing' => $routing1]);
        $this->assertTrue($response->isOk());

        $index->refresh();

        // Makes sure, document is deleted
        $response = $index->search('ruflin*');
        $this->assertEquals(1, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(0, $response->count());
    }

    /**
     * @group functional
     */
    public function testUpdateByQueryWithQueryString()
    {
        $index = $this->_createIndex();
        $type1 = new Type($index, '_doc');
        $type1->addDocument(new Document(1, ['name' => 'ruflin nicolas']));
        $type1->addDocument(new Document(2, ['name' => 'ruflin']));
        $index->refresh();

        $response = $index->search('ruflin*');
        $this->assertEquals(2, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(1, $response->count());

        // Update the element, searched by specific word. Should match first one
        $response = $index->updateByQuery('nicolas', new Script('ctx._source.name = "marc"'));
        $this->assertTrue($response->isOk());

        $index->refresh();

        // Makes sure first element is updated and renamed to marc. Should match only second
        $response = $index->search('ruflin*');
        $this->assertEquals(1, $response->count());

        $response = $index->search('marc*');
        $this->assertEquals(1, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(0, $response->count());
    }

    /**
     * @group functional
     */
    public function testUpdateByQueryAll()
    {
        $index = $this->_createIndex();
        $type1 = new Type($index, '_doc');
        $type1->addDocument(new Document(1, ['name' => 'ruflin nicolas']));
        $type1->addDocument(new Document(2, ['name' => 'ruflin']));
        $index->refresh();

        $response = $index->search('ruflin*');
        $this->assertEquals(2, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(1, $response->count());

        // Update all elements to name "marc"
        $response = $index->updateByQuery('*', new Script('ctx._source.name = "marc"'));
        $this->assertTrue($response->isOk());

        $index->refresh();

        // Because all documents have changed to marc, searching by "ruflin*" should match 0
        $response = $index->search('ruflin*');
        $this->assertEquals(0, $response->count());

        $response = $index->search('marc');
        $this->assertEquals(2, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(0, $response->count());
    }

    /**
     * @group functional
     */
    public function testDeleteIndexDeleteAlias()
    {
        $indexName = 'test';
        $aliasName = 'test-aliase';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);

        $index->create([], true);
        $index->addAlias($aliasName);

        $status = new Status($client);
        $this->assertTrue($status->indexExists($indexName));
        $this->assertTrue($status->aliasExists($aliasName));

        // Deleting index should also remove alias
        $index->delete();

        $status->refresh();
        $this->assertFalse($status->indexExists($indexName));
        $this->assertFalse($status->aliasExists($aliasName));
    }

    /**
     * @group functional
     */
    public function testAddAliasTwoIndices()
    {
        $indexName1 = 'test1';
        $aliasName = 'test-alias';

        $client = $this->_getClient();
        $index1 = $client->getIndex($indexName1);

        $index1->create([], true);
        $this->_waitForAllocation($index1);
        $index1->addAlias($aliasName);

        $index1->refresh();
        $index1->forcemerge();

        $status = new Status($client);

        $this->assertTrue($status->indexExists($indexName1));

        $this->assertTrue($status->aliasExists($aliasName));
        $this->assertTrue($index1->hasAlias($aliasName));
    }

    /**
     * @group functional
     */
    public function testReplaceAlias()
    {
        $indexName1 = 'test1';
        $aliasName = 'test-alias';

        $client = $this->_getClient();
        $index1 = $client->getIndex($indexName1);

        $index1->create([], true);
        $index1->addAlias($aliasName);

        $index1->refresh();

        $status = new Status($client);

        $this->assertTrue($status->indexExists($indexName1));
        $this->assertTrue($status->aliasExists($aliasName));
        $this->assertTrue($index1->hasAlias($aliasName));
    }

    /**
     * @group functional
     */
    public function testAddDocumentVersion()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create([], true);
        $type = new Type($index, '_doc');

        $doc1 = new Document(1);
        $doc1->set('title', 'Hello world');

        $return = $type->addDocument($doc1);
        $data = $return->getData();
        $this->assertEquals(1, $data['_version']);

        $return = $type->addDocument($doc1);
        $data = $return->getData();
        $this->assertEquals(2, $data['_version']);
    }

    /**
     * @group functional
     */
    public function testClearCache()
    {
        $index = $this->_createIndex();
        $response = $index->clearCache();
        $this->assertFalse($response->hasError());
    }

    /**
     * @group functional
     */
    public function testFlush()
    {
        $index = $this->_createIndex();
        $response = $index->flush();
        $this->assertFalse($response->hasError());
    }

    /**
     * @group functional
     */
    public function testExists()
    {
        $index = $this->_createIndex();

        $this->assertTrue($index->exists());

        $index->delete();

        $this->assertFalse($index->exists());
    }

    /**
     * Test $index->delete() return value for unknown index.
     *
     * Tests if deleting an index that does not exist in Elasticsearch,
     * correctly returns a boolean true from the hasError() method of
     * the \Elastica\Response object
     *
     * @group functional
     */
    public function testDeleteMissingIndexHasError()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('index_does_not_exist');

        try {
            $index->delete();
            $this->fail('This should never be reached. Deleting an unknown index will throw an exception');
        } catch (ResponseException $error) {
            $response = $error->getResponse();
            $this->assertTrue($response->hasError());
            $request = $error->getRequest();
            $this->assertInstanceOf(Request::class, $request);
        }
    }

    /**
     * Tests to see if the test type mapping exists when calling $index->getMapping().
     *
     * @group functional
     */
    public function testIndexGetMapping()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        $mapping = ['id' => ['type' => 'integer', 'store' => true], 'email' => ['type' => 'text'],
            'username' => ['type' => 'text'], 'test' => ['type' => 'integer'], ];

        $type->setMapping($mapping);
        $index->refresh();
        $indexMappings = $index->getMapping();

        $this->assertEquals($indexMappings['properties']['id']['type'], 'integer');
        $this->assertEquals($indexMappings['properties']['id']['store'], true);
        $this->assertEquals($indexMappings['properties']['email']['type'], 'text');
        $this->assertEquals($indexMappings['properties']['username']['type'], 'text');
        $this->assertEquals($indexMappings['properties']['test']['type'], 'integer');
    }

    /**
     * Tests to see if the index is empty when there are no types set.
     *
     * @group functional
     */
    public function testEmptyIndexGetMapping()
    {
        $index = $this->_createIndex();
        $indexMappings = $index->getMapping();

        $this->assertEmpty($indexMappings['elastica_test']);
    }

    /**
     * Test to see if search Default Limit works.
     *
     * @group functional
     */
    public function testLimitDefaultIndex()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('zero');
        $index->create(['settings' => ['index' => ['number_of_shards' => 1, 'number_of_replicas' => 0]]], true);

        $docs = [];

        $docs[] = new Document(1, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']);
        $docs[] = new Document(2, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']);
        $docs[] = new Document(3, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']);
        $docs[] = new Document(4, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']);
        $docs[] = new Document(5, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']);
        $docs[] = new Document(6, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']);
        $docs[] = new Document(7, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']);
        $docs[] = new Document(8, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']);
        $docs[] = new Document(9, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']);
        $docs[] = new Document(10, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']);
        $docs[] = new Document(11, ['id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley']);

        $type = $index->getType('_doc');
        $type->addDocuments($docs);
        $index->refresh();

        // default limit results  (default limit is 10)
        $resultSet = $index->search('farrelley');
        $this->assertEquals(10, $resultSet->count());

        // limit = 1
        $resultSet = $index->search('farrelley', 1);
        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testCreate()
    {
        $client = $this->_getClient();
        $indexName = 'test';

        //Testing recreate (backward compatibility)
        $index = $client->getIndex($indexName);
        $index->create([], true);
        $this->_waitForAllocation($index);
        $status = new Status($client);
        $this->assertTrue($status->indexExists($indexName));

        //Testing create index with array options
        $opts = ['recreate' => true];
        $index->create([], $opts);
        $this->_waitForAllocation($index);
        $status = new Status($client);
        $this->assertTrue($status->indexExists($indexName));
    }

    /**
     * @group unit
     */
    public function testCreateWithInvalidOption()
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $client = $this->_getClient();
        $indexName = 'test';
        $index = $client->getIndex($indexName);

        $opts = ['testing_invalid_option' => true];
        $index->create([], $opts);
    }

    /**
     * @group functional
     */
    public function testCreateSearch()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test');

        $query = new QueryString('test');
        $options = 5;

        $search = $index->createSearch($query, $options);

        $expected = [
            'query' => [
                'query_string' => [
                    'query' => 'test',
                ],
            ],
            'size' => 5,
        ];
        $this->assertEquals($expected, $search->getQuery()->toArray());
        $this->assertEquals(['test'], $search->getIndices());
        $this->assertTrue($search->hasIndices());
        $this->assertTrue($search->hasIndex('test'));
        $this->assertTrue($search->hasIndex($index));
        $this->assertEquals([], $search->getTypes());
        $this->assertFalse($search->hasTypes());
        $this->assertFalse($search->hasType('_doc'));

        $type = new Type($index, '_doc');
        $this->assertFalse($search->hasType($type));
    }

    /**
     * @group functional
     */
    public function testSearch()
    {
        $index = $this->_createIndex();

        $type = new Type($index, '_doc');

        $docs = [];
        $docs[] = new Document(1, ['username' => 'hans', 'test' => ['2', '3', '5']]);
        $docs[] = new Document(2, ['username' => 'john', 'test' => ['1', '3', '6']]);
        $docs[] = new Document(3, ['username' => 'rolf', 'test' => ['2', '3', '7']]);
        $type->addDocuments($docs);
        $index->refresh();

        $resultSet = $index->search('rolf');
        $this->assertEquals(1, $resultSet->count());

        $count = $index->count('rolf');
        $this->assertEquals(1, $count);

        // Test if source is returned
        $result = $resultSet->current();
        $this->assertEquals(3, $result->getId());
        $data = $result->getData();
        $this->assertEquals('rolf', $data['username']);

        $count = $index->count();
        $this->assertEquals(3, $count);
    }

    /**
     * @group functional
     */
    public function testForcemerge()
    {
        $index = $this->_createIndex('testforcemerge_indextest', false, 3);

        $type = new Type($index, '_doc');

        $docs = [];
        $docs[] = new Document(1, ['foo' => 'bar']);
        $docs[] = new Document(2, ['foo' => 'bar']);
        $type->addDocuments($docs);
        $index->refresh();

        $stats = $index->getStats()->getData();
        $this->assertEquals(2, $stats['_all']['primaries']['docs']['count']);
        $this->assertEquals(0, $stats['_all']['primaries']['docs']['deleted']);

        $type->deleteById(1);
        $index->refresh();

        $stats = $index->getStats()->getData();
        $this->assertEquals(1, $stats['_all']['primaries']['docs']['count']);

        $index->forcemerge(['max_num_segments' => 1]);

        $stats = $index->getStats()->getData();
        $this->assertEquals(1, $stats['_all']['primaries']['docs']['count']);
        $this->assertEquals(0, $stats['_all']['primaries']['docs']['deleted']);
    }

    /**
     * @group functional
     */
    public function testAnalyze()
    {
        $index = $this->_createIndex();
        $index->refresh();
        $returnedTokens = $index->analyze(['text' => 'foo']);

        $tokens = [
            [
                'token' => 'foo',
                'start_offset' => 0,
                'end_offset' => 3,
                'type' => '<ALPHANUM>',
                'position' => 0,
            ],
        ];

        $this->assertEquals($tokens, $returnedTokens);
    }

    /**
     * @group functional
     */
    public function testRequestEndpoint()
    {
        $index = $this->_createIndex();
        $index->refresh();
        $endpoint = new Analyze();
        $endpoint->setIndex('fooIndex');
        $endpoint->setBody(['text' => 'foo']);
        $returnedTokens = $index->requestEndpoint($endpoint)->getData()['tokens'];

        $tokens = [
            [
                'token' => 'foo',
                'start_offset' => 0,
                'end_offset' => 3,
                'type' => '<ALPHANUM>',
                'position' => 0,
            ],
        ];

        $this->assertEquals($tokens, $returnedTokens);
    }

    /**
     * @group functional
     */
    public function testAnalyzeExplain()
    {
        $index = $this->_createIndex();
        $index->refresh();
        $data = $index->analyze(['text' => 'foo', 'explain' => true], []);

        $this->assertArrayHasKey('custom_analyzer', $data);
    }

    /**
     * @group unit
     */
    public function testThrowExceptionIfNotScalar()
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $client = $this->_getClient();
        $client->getIndex(new \stdClass());
    }

    /**
     * @group unit
     */
    public function testConvertScalarsToString()
    {
        $client = $this->_getClient();
        $index = $client->getIndex(1);

        $this->assertEquals('1', $index->getName());
        $this->assertInternalType('string', $index->getName());
    }

    /**
     * @group functional
     */
    public function testGetEmptyAliases()
    {
        $indexName = 'test-getaliases';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);

        $index->create([], true);
        $this->_waitForAllocation($index);
        $index->refresh();
        $index->forcemerge();

        $this->assertEquals([], $index->getAliases());
    }
}
