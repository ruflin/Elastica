<?php
namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\NotFoundException;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Query\MatchAll;
use Elastica\Query\QueryString;
use Elastica\Query\SimpleQueryString;
use Elastica\Script\Script;
use Elastica\Search;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;
use Elastica\Type\Mapping;
use Elasticsearch\Endpoints\Indices\Mapping\Get;

class TypeTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testSearch()
    {
        $index = $this->_createIndex();

        $type = new Type($index, 'user');

        // Adds 1 document to the index
        $doc1 = new Document(1,
            ['username' => 'hans', 'test' => ['2', '3', '5']]
        );
        $type->addDocument($doc1);

        // Adds a list of documents with _bulk upload to the index
        $docs = [];
        $docs[] = new Document(2,
            ['username' => 'john', 'test' => ['1', '3', '6']]
        );
        $docs[] = new Document(3,
            ['username' => 'rolf', 'test' => ['2', '3', '7']]
        );
        $type->addDocuments($docs);
        $index->refresh();

        $resultSet = $type->search('rolf');
        $this->assertEquals(1, $resultSet->count());

        $count = $type->count('rolf');
        $this->assertEquals(1, $count);

        // Test if source is returned
        $result = $resultSet->current();
        $this->assertEquals(3, $result->getId());
        $data = $result->getData();
        $this->assertEquals('rolf', $data['username']);
    }

    /**
     * @group functional
     */
    public function testCreateSearch()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test_index');
        $type = new Type($index, 'test_type');

        $query = new QueryString('test');
        $options = [
            'limit' => 5,
            'explain' => true,
        ];

        $search = $type->createSearch($query, $options);

        $expected = [
            'query' => [
                'query_string' => [
                    'query' => 'test',
                ],
            ],
            'size' => 5,
            'explain' => true,
        ];
        $this->assertEquals($expected, $search->getQuery()->toArray());
        $this->assertEquals(['test_index'], $search->getIndices());
        $this->assertTrue($search->hasIndices());
        $this->assertTrue($search->hasIndex($index));
        $this->assertTrue($search->hasIndex('test_index'));
        $this->assertFalse($search->hasIndex('test'));
        $this->assertEquals(['test_type'], $search->getTypes());
        $this->assertTrue($search->hasTypes());
        $this->assertTrue($search->hasType($type));
        $this->assertTrue($search->hasType('test_type'));
        $this->assertFalse($search->hasType('test_type2'));
    }

    /**
     * @group functional
     */
    public function testCreateSearchWithArray()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test_index');
        $type = new Type($index, 'test_type');

        $query = [
            'query' => [
                'query_string' => [
                    'query' => 'test',
                ],
            ],
        ];

        $options = [
            'limit' => 5,
            'explain' => true,
        ];

        $search = $type->createSearch($query, $options);

        $expected = [
            'query' => [
                'query_string' => [
                    'query' => 'test',
                ],
            ],
            'size' => 5,
            'explain' => true,
        ];
        $this->assertEquals($expected, $search->getQuery()->toArray());
        $this->assertEquals(['test_index'], $search->getIndices());
        $this->assertTrue($search->hasIndices());
        $this->assertTrue($search->hasIndex($index));
        $this->assertTrue($search->hasIndex('test_index'));
        $this->assertFalse($search->hasIndex('test'));
        $this->assertEquals(['test_type'], $search->getTypes());
        $this->assertTrue($search->hasTypes());
        $this->assertTrue($search->hasType($type));
        $this->assertTrue($search->hasType('test_type'));
        $this->assertFalse($search->hasType('test_type2'));
    }

    /**
     * @group functional
     */
    public function testNoSource()
    {
        $index = $this->_createIndex();

        $type = new Type($index, 'user');
        $mapping = new Mapping($type, [
            'id' => ['type' => 'integer', 'store' => 'yes'],
            'username' => ['type' => 'text'],
        ]);
        $mapping->setSource(['enabled' => false]);
        $type->setMapping($mapping);

        $mapping = $type->getMapping();

        $this->assertArrayHasKey('user', $mapping);
        $this->assertArrayHasKey('properties', $mapping['user']);
        $this->assertArrayHasKey('id', $mapping['user']['properties']);
        $this->assertArrayHasKey('type', $mapping['user']['properties']['id']);
        $this->assertEquals('integer', $mapping['user']['properties']['id']['type']);

        // Adds 1 document to the index
        $doc1 = new Document(1,
            ['username' => 'hans', 'test' => ['2', '3', '5']]
        );
        $type->addDocument($doc1);

        // Adds a list of documents with _bulk upload to the index
        $docs = [];
        $docs[] = new Document(2,
            ['username' => 'john', 'test' => ['1', '3', '6']]
        );
        $docs[] = new Document(3,
            ['username' => 'rolf', 'test' => ['2', '3', '7']]
        );
        $type->addDocuments($docs);

        // To update index
        $index->refresh();

        $resultSet = $type->search('rolf');

        $this->assertEquals(1, $resultSet->count());

        // Tests if no source is in response except id
        $result = $resultSet->current();
        $this->assertEquals(3, $result->getId());
        $this->assertEmpty($result->getData());
    }

    /**
     * @group functional
     */
    public function testDeleteById()
    {
        $index = $this->_createIndex();
        $type = new Type($index, 'user');

        // Adds hans, john and rolf to the index
        $docs = [
            new Document(1, ['username' => 'hans', 'test' => ['2', '3', '5']]),
            new Document(2, ['username' => 'john', 'test' => ['1', '3', '6']]),
            new Document(3, ['username' => 'rolf', 'test' => ['2', '3', '7']]),
            new Document('foo/bar', ['username' => 'georg', 'test' => ['4', '2', '5']]),
        ];
        $type->addDocuments($docs);
        $index->refresh();

        // sanity check for rolf
        $resultSet = $type->search('rolf');
        $this->assertEquals(1, $resultSet->count());
        $data = $resultSet->current()->getData();
        $this->assertEquals('rolf', $data['username']);

        // delete rolf
        $type->deleteById(3);
        $index->refresh();

        // rolf should no longer be there
        $resultSet = $type->search('rolf');
        $this->assertEquals(0, $resultSet->count());

        // sanity check for id with slash
        $resultSet = $type->search('georg');
        $this->assertEquals(1, $resultSet->count());

        // delete georg
        $type->deleteById('foo/bar');
        $index->refresh();

        // georg should no longer be there
        $resultSet = $type->search('georg');
        $this->assertEquals(0, $resultSet->count());

        // it should not be possible to delete the entire type with this method
        try {
            $type->deleteById('');
            $this->fail('Delete with empty string id should fail');
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }

        try {
            $type->deleteById(' ');
            $this->fail('Delete with one space string id should fail');
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }

        try {
            $type->deleteById(null);
            $this->fail('Delete with null id should fail');
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }

        try {
            $type->deleteById([]);
            $this->fail('Delete with empty array id should fail');
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }

        try {
            $type->deleteById('*');
            $this->fail('Delete request should fail because of invalid id: *');
        } catch (NotFoundException $e) {
            $this->assertTrue(true);
        }

        try {
            $type->deleteById('*:*');
            $this->fail('Delete request should fail because document with id *.* does not exist');
        } catch (NotFoundException $e) {
            $this->assertTrue(true);
        }

        try {
            $type->deleteById('!');
            $this->fail('Delete request should fail because document with id ! does not exist');
        } catch (NotFoundException $e) {
            $this->assertTrue(true);
        }

        $index->refresh();

        // rolf should no longer be there
        $resultSet = $type->search('john');
        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testDeleteDocument()
    {
        $index = $this->_createIndex();
        $type = new Type($index, 'user');

        // Adds hans, john and rolf to the index
        $docs = [
            new Document(1, ['username' => 'hans', 'test' => ['2', '3', '5']]),
            new Document(2, ['username' => 'john', 'test' => ['1', '3', '6']]),
            new Document(3, ['username' => 'rolf', 'test' => ['2', '3', '7']]),
        ];
        $type->addDocuments($docs);
        $index->refresh();

        $document = $type->getDocument(1);
        $this->assertEquals(1, $document->getId());
        $this->assertEquals('hans', $document->get('username'));

        $this->assertEquals(3, $type->count());

        $type->deleteDocument($document);
        $index->refresh();

        try {
            $type->getDocument(1);
            $this->fail('Document was not deleted');
        } catch (NotFoundException $e) {
            $this->assertTrue(true);
            $this->assertEquals(2, $type->count(), 'Documents count in type should be 2');
        }
    }

    /**
     * @group functional
     * @expectedException \Elastica\Exception\NotFoundException
     */
    public function testGetDocumentNotExist()
    {
        $index = $this->_createIndex();
        $type = new Type($index, 'test');
        $type->addDocument(new Document(1, ['name' => 'ruflin']));
        $index->refresh();

        $type->getDocument(1);

        $type->getDocument(2);
    }

    /**
     * @group functional
     * @expectedException \Elastica\Exception\ResponseException
     */
    public function testGetDocumentNotExistingIndex()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'index');
        $type = new Type($index, 'type');

        $type->getDocument(1);
    }

    /**
     * @group functional
     */
    public function testDeleteByQueryWithQueryString()
    {
        $index = $this->_createIndex();
        $type = new Type($index, 'test');
        $type->addDocument(new Document(1, ['name' => 'ruflin nicolas']));
        $type->addDocument(new Document(2, ['name' => 'ruflin']));
        $index->refresh();

        $response = $index->search('ruflin*');
        $this->assertEquals(2, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(1, $response->count());

        // Delete first document
        $response = $type->deleteByQuery('nicolas');
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
        $type = new Type($index, 'test');
        $type->addDocument(new Document(1, ['name' => 'ruflin nicolas']));
        $type->addDocument(new Document(2, ['name' => 'ruflin']));
        $index->refresh();

        $response = $index->search('ruflin*');
        $this->assertEquals(2, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(1, $response->count());

        // Delete first document
        $response = $type->deleteByQuery(new SimpleQueryString('nicolas'));
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
        $type = new Type($index, 'test');
        $type->addDocument(new Document(1, ['name' => 'ruflin nicolas']));
        $type->addDocument(new Document(2, ['name' => 'ruflin']));
        $index->refresh();

        $response = $index->search('ruflin*');
        $this->assertEquals(2, $response->count());
        $response = $index->search('nicolas');
        $this->assertEquals(1, $response->count());

        // Delete first document
        $response = $type->deleteByQuery(['query' => ['query_string' => ['query' => 'nicolas']]]);
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
        $type = new Type($index, 'test');
        $doc = new Document(1, ['name' => 'ruflin nicolas']);
        $doc->setRouting('first_routing');
        $type->addDocument($doc);
        $doc = new Document(2, ['name' => 'ruflin']);
        $doc->setRouting('second_routing');
        $type->addDocument($doc);
        $index->refresh();

        $response = $index->search('ruflin*');
        $this->assertEquals(2, $response->count());

        $response = $index->search('ruflin*', ['routing' => 'first_routing']);
        $this->assertEquals(1, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(1, $response->count());

        // Route to the wrong document id; should not delete
        $response = $type->deleteByQuery(new SimpleQueryString('nicolas'), ['routing' => 'second_routing']);
        $this->assertTrue($response->isOk());

        $index->refresh();

        $response = $index->search('ruflin*');
        $this->assertEquals(2, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(1, $response->count());

        // Delete first document
        $response = $type->deleteByQuery(new SimpleQueryString('nicolas'), ['routing' => 'first_routing']);
        $this->assertTrue($response->isOk());

        $index->refresh();

        // Makes sure, document is deleted
        $response = $index->search('ruflin*');
        $this->assertEquals(1, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(0, $response->count());
    }

    /**
     * Test to see if Elastica_Type::getDocument() is properly using
     * the fields array when available instead of _source.
     *
     * @group functional
     */
    public function testGetDocumentWithFieldsSelection()
    {
        $index = $this->_createIndex();

        $type = new Type($index, 'test');
        $mapping = new Mapping();
        $mapping->setProperties([
            'name' => [
                'type' => 'string',
                'store' => 'yes', ],
            'email' => [
                'type' => 'string',
                'store' => 'yes', ],
            'country' => [
                'type' => 'string',
            ],
        ]);

        $mapping->disableSource();
        $type->setMapping($mapping);

        $type->addDocument(new Document(1, ['name' => 'loris', 'country' => 'FR', 'email' => 'test@test.com']));
        $index->refresh();

        $document = $type->getDocument(1, ['stored_fields' => 'name,email']);
        $data = $document->getData();

        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayNotHasKey('country', $data);
    }

    /**
     * Test to see if search Default Limit works.
     *
     * @group functional
     */
    public function testLimitDefaultType()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('zero');
        $index->create(['index' => ['number_of_shards' => 1, 'number_of_replicas' => 0]], true);

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

        $type = $index->getType('zeroType');
        $type->addDocuments($docs);
        $index->refresh();

        // default results  (limit default is 10)
        $resultSet = $type->search('farrelley');
        $this->assertEquals(10, $resultSet->count());

        // limit = 1
        $resultSet = $type->search('farrelley', 1);
        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testUpdateDocument()
    {
        $this->_checkScriptInlineSetting();
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_test');
        $type = $index->getType('update_type');
        $id = 1;
        $type->addDocument(new Document($id, ['name' => 'bruce wayne batman', 'counter' => 1]));
        $newName = 'batman';

        $document = new Document();
        $script = new Script(
            'ctx._source.name = params.name; ctx._source.counter += params.count',
            [
                'name' => $newName,
                'count' => 2,
            ],
            Script::LANG_PAINLESS,
            $id
        );
        $script->setUpsert($document);

        $type->updateDocument($script);
        $updatedDoc = $type->getDocument($id)->getData();
        $this->assertEquals($newName, $updatedDoc['name'], 'Name was not updated');
        $this->assertEquals(3, $updatedDoc['counter'], 'Counter was not incremented');
    }

    /**
     * @group functional
     */
    public function testUpdateDocumentWithIdForwardSlashes()
    {
        $this->_checkScriptInlineSetting();
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_test');
        $type = $index->getType('update_type');
        $id = '/id/with/forward/slashes';
        $type->addDocument(new Document($id, ['name' => 'bruce wayne batman', 'counter' => 1]));
        $newName = 'batman';

        $document = new Document();
        $script = new Script(
            'ctx._source.name = params.name; ctx._source.counter += params.count',
            [
                'name' => $newName,
                'count' => 2,
            ],
            Script::LANG_PAINLESS,
            $id
        );
        $script->setUpsert($document);

        $type->updateDocument($script);
        $updatedDoc = $type->getDocument($id)->getData();
        $this->assertEquals($newName, $updatedDoc['name'], 'Name was not updated');
        $this->assertEquals(3, $updatedDoc['counter'], 'Counter was not incremented');
    }

    /**
     * @group functional
     */
    public function testUpdateDocumentWithParameter()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_test');
        $type = $index->getType('update_type');
        $id = 1;
        $type->addDocument(new Document($id, ['name' => 'bruce wayne batman', 'counter' => 1]));
        $newName = 'batman';

        $document = new Document();
        $script = new Script(
            'ctx._source.name = params.name; ctx._source.counter += params.count',
            [
                'name' => $newName,
                'count' => 2,
            ],
            Script::LANG_PAINLESS,
            $id
        );
        $script->setUpsert($document);

        try {
            $type->updateDocument($script, ['version' => 999]); // Wrong version number to make the update fail
        } catch (ResponseException $e) {
            $error = $e->getResponse()->getFullError();
            $this->assertContains('version_conflict_engine_exception', $error['type']);
        }
        $updatedDoc = $type->getDocument($id)->getData();
        $this->assertNotEquals($newName, $updatedDoc['name'], 'Name was updated');
        $this->assertNotEquals(3, $updatedDoc['counter'], 'Counter was incremented');
    }

    /**
     * @group functional
     */
    public function testUpdateDocumentWithFieldsSource()
    {
        $this->_checkScriptInlineSetting();
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_test');
        $type = $index->getType('update_type');

        $client->setConfigValue('document', ['autoPopulate' => true]);

        $newDocument = new Document(null, ['counter' => 5, 'name' => 'Batman']);

        $this->assertFalse($newDocument->hasVersion());

        $response = $type->addDocument($newDocument);
        $responseData = $response->getData();

        $this->assertTrue($newDocument->hasVersion());
        $this->assertArrayHasKey('_version', $responseData, '_version is missing in response data it is weird');
        $this->assertEquals(1, $responseData['_version']);
        $this->assertEquals($responseData['_version'], $newDocument->getVersion());

        $this->assertTrue($newDocument->hasId());

        $script = new Script('ctx._source.counter += params.count; ctx._source.realName = params.realName', null, Script::LANG_PAINLESS);
        $script->setId($newDocument->getId());
        $script->setParam('count', 7);
        $script->setParam('realName', 'Bruce Wayne');
        $script->setUpsert($newDocument);

        $newDocument->setFieldsSource();

        $response = $type->updateDocument($script);
        $responseData = $response->getData();

        $data = $type->getDocument($newDocument->getId())->getData();

        $this->assertEquals(12, $data['counter']);
        $this->assertEquals('Batman', $data['name']);
        $this->assertEquals('Bruce Wayne', $data['realName']);

        $this->assertTrue($newDocument->hasVersion());
        $this->assertArrayHasKey('_version', $responseData, '_version is missing in response data it is weird');
        $this->assertEquals(2, $responseData['_version']);

        $document = $type->getDocument($newDocument->getId());
    }

    /**
     * @group functional
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testUpdateDocumentWithoutId()
    {
        $index = $this->_createIndex();
        $this->_waitForAllocation($index);
        $type = $index->getType('elastica_type');

        $document = new Document();

        $type->updateDocument($document);
    }

    /**
     * @group functional
     */
    public function testUpdateDocumentWithoutSource()
    {
        $index = $this->_createIndex();
        $type = $index->getType('elastica_type');

        $mapping = new Mapping();
        $mapping->setProperties([
            'name' => [
                'type' => 'text',
                'store' => 'true', ],
            'counter' => [
                'type' => 'integer',
            ],
        ]);
        $mapping->disableSource();
        $type->setMapping($mapping);

        $newDocument = new Document();
        $newDocument->setAutoPopulate();
        $newDocument->set('name', 'Batman');
        $newDocument->set('counter', 1);

        $type->addDocument($newDocument);

        $script = new Script('ctx._source.counter += count; ctx._source.name = name');
        $script->setId($newDocument->getId());
        $script->setParam('count', 2);
        $script->setParam('name', 'robin');

        $script->setUpsert($newDocument);

        try {
            $type->updateDocument($script);
            $this->fail('Update request should fail because source is disabled. Fields param is not set');
        } catch (ResponseException $e) {
            $error = $e->getResponse()->getFullError();
            $this->assertContains('document_source_missing_exception', $error['type']);
        }

        $newDocument->setFieldsSource();

        try {
            $type->updateDocument($newDocument);
            $this->fail('Update request should fail because source is disabled. Fields param is set to _source');
        } catch (ResponseException $e) {
            $error = $e->getResponse()->getFullError();
            $this->assertContains('document_source_missing_exception', $error['type']);
        }
    }

    /**
     * @group functional
     */
    public function testAddDocumentHashId()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test2');

        $hashId = '#1';

        $doc = new Document($hashId, ['name' => 'ruflin']);
        $type->addDocument($doc);

        $index->refresh();

        $search = new Search($index->getClient());
        $search->addIndex($index);
        $resultSet = $search->search(new MatchAll());
        $this->assertEquals($hashId, $resultSet->current()->getId());

        $doc = $type->getDocument($hashId);
        $this->assertEquals($hashId, $doc->getId());
    }

    /**
     * @group functional
     */
    public function testAddDocumentAutoGeneratedId()
    {
        $index = $this->_createIndex();
        $type = $index->getType('elastica_type');

        $document = new Document();
        $document->setAutoPopulate();
        $document->set('name', 'ruflin');
        $this->assertEquals('', $document->getId());
        $this->assertFalse($document->hasId());

        $type->addDocument($document);

        $this->assertNotEquals('', $document->getId());
        $this->assertTrue($document->hasId());

        $foundDoc = $type->getDocument($document->getId());
        $this->assertInstanceOf(Document::class, $foundDoc);
        $this->assertEquals($document->getId(), $foundDoc->getId());
        $data = $foundDoc->getData();
        $this->assertArrayHasKey('name', $data);
        $this->assertEquals('ruflin', $data['name']);
    }

    /**
     * @group functional
     * @expectedException \Elastica\Exception\RuntimeException
     */
    public function testAddDocumentWithoutSerializer()
    {
        $index = $this->_createIndex();
        $this->_waitForAllocation($index);

        $type = new Type($index, 'user');

        $type->addObject(new \stdClass());
    }

    /**
     * @group functional
     */
    public function testAddObject()
    {
        $index = $this->_createIndex();

        $type = new Type($index, 'user');
        $type->setSerializer('get_object_vars');

        $userObject = new \stdClass();
        $userObject->username = 'hans';
        $userObject->test = ['2', '3', '5'];

        $type->addObject($userObject);

        $index->refresh();

        $resultSet = $type->search('hans');
        $this->assertEquals(1, $resultSet->count());

        // Test if source is returned
        $result = $resultSet->current();
        $data = $result->getData();
        $this->assertEquals('hans', $data['username']);
    }

    /**
     * @group unit
     */
    public function testSetSerializer()
    {
        $index = $this->_getClient()->getIndex('foo');
        $type = $index->getType('user');
        $ret = $type->setSerializer('get_object_vars');
        $this->assertInstanceOf(Type::class, $ret);
    }

    /**
     * @group functional
     */
    public function testExists()
    {
        $index = $this->_createIndex();
        $this->assertTrue($index->exists());

        $type = new Type($index, 'user');
        $this->assertFalse($type->exists());

        $type->addDocument(new Document(1, ['name' => 'test name']));
        $index->forcemerge();

        // sleep a moment to be sure that all nodes in cluster has new type
        sleep(5);

        //Test if type exists
        $this->assertTrue($type->exists());

        $index->delete();
        $this->assertFalse($index->exists());
    }

    /**
     * @group functional
     */
    public function testGetMapping()
    {
        $typeName = 'test-type';

        $index = $this->_createIndex();
        $indexName = $index->getName();
        $type = new Type($index, $typeName);
        $mapping = new Mapping($type, $expect = [
            'id' => ['type' => 'integer', 'store' => true],
        ]);
        $type->setMapping($mapping);

        $client = $index->getClient();

        $this->assertEquals(
            ['test-type' => ['properties' => $expect]],
            $client->getIndex($indexName)->getType($typeName)->getMapping()
        );
    }

    /**
     * @group functional
     */
    public function testGetMappingAlias()
    {
        $aliasName = 'test-alias';
        $typeName = 'test-alias-type';

        $index = $this->_createIndex();
        $index->addAlias($aliasName);
        $type = new Type($index, $typeName);
        $mapping = new Mapping($type, $expect = [
            'id' => ['type' => 'integer', 'store' => true],
        ]);
        $type->setMapping($mapping);

        $client = $index->getClient();

        $this->assertEquals(
            ['test-alias-type' => ['properties' => $expect]],
            $client->getIndex($aliasName)->getType($typeName)->getMapping()
        );
    }

    /**
     * @group functional
     */
    public function testRequestEndpoint()
    {
        $index = $this->_createIndex();
        $type = new Type($index, 'foo');

        $mapping = new Mapping($type, $expect = [
            'id' => ['type' => 'integer', 'store' => true],
        ]);
        $type->setMapping($mapping);

        $endpoint = new Get();
        $endpoint->setIndex('nonExistsIndex');
        $endpoint->setType('nonExistsType');

        $response = $type->requestEndpoint($endpoint);
        $data = $response->getData();
        $mapping = array_shift($data);

        $this->assertEquals(
            ['foo' => ['properties' => $expect]],
            $mapping['mappings']
        );
    }

    /**
     * @group functional
     *
     * @expectedException \Elastica\Exception\ResponseException
     */
    public function testExceptionMappingOnFieldsWithSameName()
    {
        $index = $this->_createIndex();
        $type1 = new Type($index, 'foo');
        $type2 = new Type($index, 'bar');

        $mapping = new Mapping(null, $expect = [
            'text' => ['type' => 'text', 'analyzer' => 'standard'],
        ]);
        $type1->setMapping($mapping);
        $type2->setMapping($mapping);

        $mapping2 = new Mapping($type1, $expect = [
            'text' => ['type' => 'text', 'analyzer' => 'standard', 'search_analyzer' => 'whitespace'],
        ]);
        $type1->setMapping($mapping2);
    }

    /**
     * @group functional
     */
    public function testMappingOnFieldsWithSameName()
    {
        $index = $this->_createIndex();
        $type1 = new Type($index, 'foo');
        $type2 = new Type($index, 'bar');

        $mapping = new Mapping(null, $expect = [
            'text' => ['type' => 'text', 'analyzer' => 'standard'],
        ]);
        $type1->setMapping($mapping);
        $type2->setMapping($mapping);

        $mapping2 = new Mapping($type1, $expect = [
            'text' => ['type' => 'text', 'analyzer' => 'standard', 'search_analyzer' => 'whitespace'],
        ]);
        $type1->setMapping($mapping2, ['update_all_types' => 'update_all_types']);
        $this->assertTrue(true);
    }
}
