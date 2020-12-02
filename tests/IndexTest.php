<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query\QueryString;
use Elastica\Query\SimpleQueryString;
use Elastica\Query\Term;
use Elastica\Request;
use Elastica\Script\Script;
use Elastica\Status;
use Elastica\Test\Base as BaseTest;
use Elasticsearch\Endpoints\Indices\Analyze;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * @internal
 */
class IndexTest extends BaseTest
{
    use ExpectDeprecationTrait;

    /**
     * @group functional
     */
    public function testMapping(): void
    {
        $index = $this->_createIndex();

        $mappings = new Mapping([
            'id' => ['type' => 'integer', 'store' => true],
            'email' => ['type' => 'text'],
            'username' => ['type' => 'text'],
            'test' => ['type' => 'integer'],
        ]);
        $index->setMapping($mappings);
        $index->addDocument(
            new Document(1, ['id' => 1, 'email' => 'test@test.com', 'username' => 'hanswurst', 'test' => ['2', '3', '5']])
        );
        $index->forcemerge();

        $storedMapping = $index->getMapping();

        $this->assertEquals('integer', $storedMapping['properties']['id']['type']);
        $this->assertEquals(true, $storedMapping['properties']['id']['store']);
        $this->assertEquals('text', $storedMapping['properties']['email']['type']);
        $this->assertEquals('text', $storedMapping['properties']['username']['type']);
        $this->assertEquals('integer', $storedMapping['properties']['test']['type']);
    }

    /**
     * @group functional
     */
    public function testGetMappingAlias(): void
    {
        $index = $this->_createIndex();
        $indexName = $index->getName();

        $aliasName = 'test-mapping-alias';
        $index->addAlias($aliasName);

        $mapping = new Mapping(['id' => ['type' => 'integer', 'store' => 'true']]);
        $index->setMapping($mapping);

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
    public function testAddRemoveAlias(): void
    {
        $this->expectException(ResponseException::class);

        $client = $this->_getClient();

        $indexName1 = 'test1';
        $aliasName = 'test-alias';

        $index = $client->getIndex($indexName1);
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
        $index->addDocument(new Document(1, ['id' => 1, 'email' => 'test@test.com', 'username' => 'ruflin']));
        $index->refresh();

        $resultSet = $index->search('ruflin');
        $this->assertEquals(1, $resultSet->count());

        $data = $index->addAlias($aliasName, true)->getData();
        $this->assertTrue($data['acknowledged']);

        $response = $index->removeAlias($aliasName)->getData();
        $this->assertTrue($response['acknowledged']);

        $client->getIndex($aliasName)->search('ruflin');
    }

    /**
     * @group functional
     */
    public function testCount(): void
    {
        $index = $this->_createIndex();

        // Add document to normal index
        $doc1 = new Document(null, ['name' => 'ruflin']);
        $doc2 = new Document(null, ['name' => 'nicolas']);

        $index->addDocument($doc1);
        $index->addDocument($doc2);

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
    public function testCountGet(): void
    {
        $index = $this->_createIndex();

        // Add document to normal index
        $doc1 = new Document(null, ['name' => 'ruflin']);
        $doc2 = new Document(null, ['name' => 'nicolas']);

        $index->addDocument($doc1);
        $index->addDocument($doc2);

        $index->refresh();

        $this->assertEquals(2, $index->count('', Request::GET));

        $query = new Term();
        $key = 'name';
        $value = 'nicolas';
        $query->setTerm($key, $value);

        $this->assertEquals(1, $index->count($query, Request::GET));
    }

    /**
     * @group functional
     */
    public function testDeleteByQueryWithQueryString(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document(1, ['name' => 'ruflin nicolas']),
            new Document(2, ['name' => 'ruflin']),
        ]);
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
    public function testDeleteByQueryWithQuery(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document(1, ['name' => 'ruflin nicolas']),
            new Document(2, ['name' => 'ruflin']),
        ]);
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
    public function testDeleteByQueryWithArrayQuery(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document(1, ['name' => 'ruflin nicolas']),
            new Document(2, ['name' => 'ruflin']),
        ]);
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
    public function testDeleteByQueryWithQueryAndOptions(): void
    {
        $index = $this->_createIndex(null, true, 2);

        $routing1 = 'first_routing';
        $routing2 = 'second_routing';

        $doc = new Document(1, ['name' => 'ruflin nicolas']);
        $doc->setRouting($routing1);
        $index->addDocument($doc);

        $doc = new Document(2, ['name' => 'ruflin']);
        $doc->setRouting($routing1);
        $index->addDocument($doc);

        $doc = new Document(2, ['name' => 'ruflin']);
        $doc->setRouting($routing1);
        $index->addDocument($doc);

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
    public function testUpdateByQueryWithQueryString(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document(1, ['name' => 'ruflin nicolas']),
            new Document(2, ['name' => 'ruflin']),
        ]);
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
    public function testUpdateByQueryAll(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document(1, ['name' => 'ruflin nicolas']),
            new Document(2, ['name' => 'ruflin']),
        ]);
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
    public function testDeleteIndexDeleteAlias(): void
    {
        $indexName = 'test';
        $aliasName = 'test-aliase';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);

        $index->create([], [
            'recreate' => true,
        ]);
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
    public function testAddAliasTwoIndices(): void
    {
        $indexName1 = 'test1';
        $aliasName = 'test-alias';

        $client = $this->_getClient();
        $index1 = $client->getIndex($indexName1);

        $index1->create([], [
            'recreate' => true,
        ]);
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
    public function testReplaceAlias(): void
    {
        $indexName1 = 'test1';
        $aliasName = 'test-alias';

        $client = $this->_getClient();
        $index1 = $client->getIndex($indexName1);

        $index1->create([], [
            'recreate' => true,
        ]);
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
    public function testAddDocumentVersion(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create([], [
            'recreate' => true,
        ]);

        $doc1 = new Document(1);
        $doc1->set('title', 'Hello world');

        $return = $index->addDocument($doc1);
        $data = $return->getData();
        $this->assertEquals(1, $data['_version']);

        $return = $index->addDocument($doc1);
        $data = $return->getData();
        $this->assertEquals(2, $data['_version']);
    }

    /**
     * @group functional
     */
    public function testClearCache(): void
    {
        $index = $this->_createIndex();
        $response = $index->clearCache();
        $this->assertFalse($response->hasError());
    }

    /**
     * @group functional
     */
    public function testFlush(): void
    {
        $index = $this->_createIndex();
        $response = $index->flush();
        $this->assertFalse($response->hasError());
    }

    /**
     * @group functional
     */
    public function testExists(): void
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
    public function testDeleteMissingIndexHasError(): void
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
    public function testIndexGetMapping(): void
    {
        $index = $this->_createIndex();
        $mappings = new Mapping([
            'id' => ['type' => 'integer', 'store' => true],
            'email' => ['type' => 'text'],
            'username' => ['type' => 'text'],
            'test' => ['type' => 'integer'],
        ]);

        $index->setMapping($mappings);
        $index->refresh();
        $indexMappings = $index->getMapping();

        $this->assertEquals('integer', $indexMappings['properties']['id']['type']);
        $this->assertEquals(true, $indexMappings['properties']['id']['store']);
        $this->assertEquals('text', $indexMappings['properties']['email']['type']);
        $this->assertEquals('text', $indexMappings['properties']['username']['type']);
        $this->assertEquals('integer', $indexMappings['properties']['test']['type']);
    }

    /**
     * @group functional
     */
    public function testEmptyIndexGetMapping(): void
    {
        $indexMappings = $this->_createIndex()->getMapping();

        $this->assertEmpty($indexMappings);
    }

    /**
     * Test to see if search Default Limit works.
     *
     * @group functional
     */
    public function testLimitDefaultIndex(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('zero');
        $index->create(['settings' => ['index' => ['number_of_shards' => 1, 'number_of_replicas' => 0]]]);

        $docs = [
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
        ];
        $index->addDocuments($docs);
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
    public function testCreate(): void
    {
        $client = $this->_getClient();
        $indexName = 'test';

        $index = $client->getIndex($indexName);
        $index->create([], [
            'wait_for_active_shards' => 'all',
        ]);
        $status = new Status($client);
        $this->assertTrue($status->indexExists($indexName));

        $index = $client->getIndex($indexName);
        $index->create([], [
            'recreate' => true,
            'wait_for_active_shards' => 'all',
        ]);
        $status = new Status($client);
        $this->assertTrue($status->indexExists($indexName));
    }

    /**
     * @group functional
     * @group legacy
     */
    public function testLegacyCreate(): void
    {
        $client = $this->_getClient();
        $indexName = 'test';
        $index = $client->getIndex($indexName);

        $this->expectDeprecation('Since ruflin/elastica 7.1.0: Passing null as 2nd argument to "Elastica\Index::create()" is deprecated, avoid passing this argument or pass an array instead. It will be removed in 8.0.');
        $index->create([], null);
        $status = new Status($client);
        $this->assertTrue($status->indexExists($indexName));

        $this->expectDeprecation('Since ruflin/elastica 7.1.0: Passing a bool as 2nd argument to "Elastica\Index::create()" is deprecated, pass an array with the key "recreate" instead. It will be removed in 8.0.');
        $index->create([], true);
        $status = new Status($client);
        $this->assertTrue($status->indexExists($indexName));
    }

    /**
     * @group unit
     */
    public function testCreateWithInvalidOption(): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessageMatches('/"testing_invalid_option" is not a valid option\. Allowed options are ((("[a-z_]+")(, )?)+)\./');

        $client = $this->createMock(Client::class);
        $index = new Index($client, 'test');

        $opts = ['testing_invalid_option' => true];
        $index->create([], $opts);
    }

    /**
     * @group unit
     */
    public function testCreateSearch(): void
    {
        $client = $this->createMock(Client::class);
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
    }

    /**
     * @group functional
     */
    public function testSearch(): void
    {
        $index = $this->_createIndex();

        $docs = [];
        $docs[] = new Document(1, ['username' => 'hans', 'test' => ['2', '3', '5']]);
        $docs[] = new Document(2, ['username' => 'john', 'test' => ['1', '3', '6']]);
        $docs[] = new Document(3, ['username' => 'rolf', 'test' => ['2', '3', '7']]);
        $index->addDocuments($docs);
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
    public function testSearchGet(): void
    {
        $index = $this->_createIndex();
        $docs = [];
        $docs[] = new Document(1, ['username' => 'hans']);
        $index->addDocuments($docs);
        $index->refresh();

        $resultSet = $index->search('hans', null, Request::GET);
        $this->assertEquals(1, $resultSet->count());

        $count = $index->count('hans', Request::GET);
        $this->assertEquals(1, $count);
    }

    /**
     * @group functional
     */
    public function testForcemerge(): void
    {
        $index = $this->_createIndex('testforcemerge_indextest', false, 3);

        $docs = [];
        $docs[] = new Document(1, ['foo' => 'bar']);
        $docs[] = new Document(2, ['foo' => 'bar']);
        $index->addDocuments($docs);
        $index->refresh();

        $stats = $index->getStats()->getData();
        $this->assertEquals(2, $stats['_all']['primaries']['docs']['count']);
        $this->assertEquals(0, $stats['_all']['primaries']['docs']['deleted']);

        $index->deleteById('1');
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
    public function testAnalyze(): void
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
    public function testRequestEndpoint(): void
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
    public function testAnalyzeExplain(): void
    {
        $index = $this->_createIndex();
        $index->refresh();
        $data = $index->analyze(['text' => 'foo', 'explain' => true], []);

        $this->assertArrayHasKey('custom_analyzer', $data);
    }

    /**
     * @group functional
     */
    public function testGetEmptyAliases(): void
    {
        $indexName = 'test-getaliases';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);

        $index->create([], [
            'recreate' => true,
        ]);
        $this->_waitForAllocation($index);
        $index->refresh();
        $index->forcemerge();

        $this->assertEquals([], $index->getAliases());
    }
}
