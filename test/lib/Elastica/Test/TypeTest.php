<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\NotFoundException;
use Elastica\Exception\ResponseException;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Query\SimpleQueryString;
use Elastica\Script;
use Elastica\Search;
use Elastica\Filter\Term;
use Elastica\Type;
use Elastica\Index;
use Elastica\Type\Mapping;
use Elastica\Test\Base as BaseTest;

class TypeTest extends BaseTest
{
    public function testSearch()
    {
        $index = $this->_createIndex();

        $type = new Type($index, 'user');

        // Adds 1 document to the index
        $doc1 = new Document(1,
            array('username' => 'hans', 'test' => array('2', '3', '5'))
        );
        $type->addDocument($doc1);

        // Adds a list of documents with _bulk upload to the index
        $docs = array();
        $docs[] = new Document(2,
            array('username' => 'john', 'test' => array('1', '3', '6'))
        );
        $docs[] = new Document(3,
            array('username' => 'rolf', 'test' => array('2', '3', '7'))
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

    public function testCreateSearch()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test_index');
        $type = new Type($index, 'test_type');

        $query = new Query\QueryString('test');
        $options = array(
            'limit' => 5,
            'explain' => true,
        );

        $search = $type->createSearch($query, $options);

        $expected = array(
            'query' => array(
                'query_string' => array(
                    'query' => 'test'
                )
            ),
            'size' => 5,
            'explain' => true
        );
        $this->assertEquals($expected, $search->getQuery()->toArray());
        $this->assertEquals(array('test_index'), $search->getIndices());
        $this->assertTrue($search->hasIndices());
        $this->assertTrue($search->hasIndex($index));
        $this->assertTrue($search->hasIndex('test_index'));
        $this->assertFalse($search->hasIndex('test'));
        $this->assertEquals(array('test_type'), $search->getTypes());
        $this->assertTrue($search->hasTypes());
        $this->assertTrue($search->hasType($type));
        $this->assertTrue($search->hasType('test_type'));
        $this->assertFalse($search->hasType('test_type2'));
    }

    public function testCreateSearchWithArray()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test_index');
        $type = new Type($index, 'test_type');

        $query = array(
            'query' => array(
                'query_string' => array(
                    'query' => 'test'
                )
            )
        );

        $options = array(
            'limit' => 5,
            'explain' => true,
        );

        $search = $type->createSearch($query, $options);

        $expected = array(
            'query' => array(
                'query_string' => array(
                    'query' => 'test'
                )
            ),
            'size' => 5,
            'explain' => true
        );
        $this->assertEquals($expected, $search->getQuery()->toArray());
        $this->assertEquals(array('test_index'), $search->getIndices());
        $this->assertTrue($search->hasIndices());
        $this->assertTrue($search->hasIndex($index));
        $this->assertTrue($search->hasIndex('test_index'));
        $this->assertFalse($search->hasIndex('test'));
        $this->assertEquals(array('test_type'), $search->getTypes());
        $this->assertTrue($search->hasTypes());
        $this->assertTrue($search->hasType($type));
        $this->assertTrue($search->hasType('test_type'));
        $this->assertFalse($search->hasType('test_type2'));
    }

    public function testNoSource()
    {
        $index = $this->_createIndex();

        $type = new Type($index, 'user');
        $mapping = new Mapping($type, array(
                'id' => array('type' => 'integer', 'store' => 'yes'),
                'username' => array('type' => 'string', 'store' => 'no'),
            ));
        $mapping->setSource(array('enabled' => false));
        $type->setMapping($mapping);

        $mapping = $type->getMapping();

        $this->assertArrayHasKey('user', $mapping);
        $this->assertArrayHasKey('properties', $mapping['user']);
        $this->assertArrayHasKey('id', $mapping['user']['properties']);
        $this->assertArrayHasKey('type', $mapping['user']['properties']['id']);
        $this->assertEquals('integer', $mapping['user']['properties']['id']['type']);

        // Adds 1 document to the index
        $doc1 = new Document(1,
            array('username' => 'hans', 'test' => array('2', '3', '5'))
        );
        $type->addDocument($doc1);

        // Adds a list of documents with _bulk upload to the index
        $docs = array();
        $docs[] = new Document(2,
            array('username' => 'john', 'test' => array('1', '3', '6'))
        );
        $docs[] = new Document(3,
            array('username' => 'rolf', 'test' => array('2', '3', '7'))
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

    public function testDeleteById()
    {
        $index = $this->_createIndex();
        $type = new Type($index, 'user');

        // Adds hans, john and rolf to the index
        $docs = array(
            new Document(1, array('username' => 'hans', 'test' => array('2', '3', '5'))),
            new Document(2, array('username' => 'john', 'test' => array('1', '3', '6'))),
            new Document(3, array('username' => 'rolf', 'test' => array('2', '3', '7'))),
            new Document('foo/bar', array('username' => 'georg', 'test' => array('4', '2', '5'))),
        );
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
            $type->deleteById(array());
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

    public function testDeleteDocument()
    {
        $index = $this->_createIndex();
        $type = new Type($index, 'user');

        // Adds hans, john and rolf to the index
        $docs = array(
            new Document(1, array('username' => 'hans', 'test' => array('2', '3', '5'))),
            new Document(2, array('username' => 'john', 'test' => array('1', '3', '6'))),
            new Document(3, array('username' => 'rolf', 'test' => array('2', '3', '7'))),
        );
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
     * @expectedException \Elastica\Exception\NotFoundException
     */
    public function testGetDocumentNotExist()
    {
        $index = $this->_createIndex();
        $type = new Type($index, 'test');
        $type->addDocument(new Document(1, array('name' => 'ruflin')));
        $index->refresh();

        $type->getDocument(1);

        $type->getDocument(2);
    }

    /**
     * @expectedException \Elastica\Exception\NotFoundException
     */
    public function testGetDocumentNotExistingIndex()
    {
        $client = new Client();
        $index = new Index($client, 'index');
        $type = new Type($index, 'type');

        $type->getDocument(1);
    }

    public function testDeleteByQueryWithQueryString()
    {
        $index = $this->_createIndex();
        $type = new Type($index, 'test');
        $type->addDocument(new Document(1, array('name' => 'ruflin nicolas')));
        $type->addDocument(new Document(2, array('name' => 'ruflin')));
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

    public function testDeleteByQueryWithQuery()
    {
        $index = $this->_createIndex();
        $type = new Type($index, 'test');
        $type->addDocument(new Document(1, array('name' => 'ruflin nicolas')));
        $type->addDocument(new Document(2, array('name' => 'ruflin')));
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

    public function testDeleteByQueryWithQueryAndOptions()
    {
        $index = $this->_createIndex('test', true, 2);
        $type = new Type($index, 'test');
        $type->addDocument(new Document(1, array('name' => 'ruflin nicolas')));
        $type->addDocument(new Document(2, array('name' => 'ruflin')));
        $index->refresh();

        $response = $index->search('ruflin*');
        $this->assertEquals(2, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(1, $response->count());

        // Route to the wrong document id; should not delete
        $response = $type->deleteByQuery(new SimpleQueryString('nicolas'), array('routing'=>'2'));
        $this->assertTrue($response->isOk());

        $index->refresh();

        $response = $index->search('ruflin*');
        $this->assertEquals(2, $response->count());

        $response = $index->search('nicolas');
        $this->assertEquals(1, $response->count());

        // Delete first document
        $response = $type->deleteByQuery(new SimpleQueryString('nicolas'), array('routing'=>'1'));
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
     * the fields array when available instead of _source
     */
    public function testGetDocumentWithFieldsSelection()
    {
        $index = $this->_createIndex();
        $type = new Type($index, 'test');
        $type->addDocument(new Document(1, array('name' => 'loris', 'country' => 'FR', 'email' => 'test@test.com')));
        $index->refresh();

        $document = $type->getDocument(1, array('fields' => 'name,email'));
        $data = $document->getData();

        $this->assertArrayHasKey('name', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayNotHasKey('country', $data);
    }

    /**
     * Test to see if search Default Limit works
     */
    public function testLimitDefaultType()
    {
        $client = $this->_getClient();
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

        // default results  (limit default is 10)
        $resultSet = $type->search('farrelley');
        $this->assertEquals(10, $resultSet->count());

        // limit = 1
        $resultSet = $type->search('farrelley', 1);
        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * Test Delete of index type.  After delete will check for type mapping.
     */
    public function testDeleteType()
    {
        $index = $this->_createIndex();
        $type = new Type($index, 'test');
        $type->addDocument(new Document(1, array('name' => 'ruflin nicolas')));
        $type->addDocument(new Document(2, array('name' => 'ruflin')));
        $index->refresh();

        $type->delete();
        $this->assertFalse($type->exists());
    }

    public function testMoreLikeThisApi()
    {
        $client = new Client(array('persistent' => false));
        $index = $client->getIndex('elastica_test');
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);

        $type = new Type($index, 'mlt_test');
        $type->addDocument(new Document(1, array('visible' => true, 'name' => 'bruce wayne batman')));
        $type->addDocument(new Document(2, array('visible' => true, 'name' => 'bruce wayne')));
        $type->addDocument(new Document(3, array('visible' => false, 'name' => 'bruce wayne')));
        $type->addDocument(new Document(4, array('visible' => true, 'name' => 'batman')));
        $type->addDocument(new Document(5, array('visible' => false, 'name' => 'batman')));
        $type->addDocument(new Document(6, array('visible' => true, 'name' => 'superman')));
        $type->addDocument(new Document(7, array('visible' => true, 'name' => 'spiderman')));

        $index->refresh();

        $document = $type->getDocument(1);

        // Return all similar
        $resultSet = $type->moreLikeThis($document, array('min_term_freq' => '1', 'min_doc_freq' => '1'));
        $this->assertEquals(4, $resultSet->count());

        // Return just the visible similar
        $query              = new Query();
        $filterTerm         = new Term();
        $filterTerm->setTerm('visible', true);
        $query->setPostFilter($filterTerm);

        $resultSet = $type->moreLikeThis($document, array('min_term_freq' => '1', 'min_doc_freq' => '1'), $query);
        $this->assertEquals(2, $resultSet->count());
    }

    public function testUpdateDocument()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_test');
        $type = $index->getType('update_type');
        $id = 1;
        $type->addDocument(new Document($id, array('name' => 'bruce wayne batman', 'counter' => 1)));
        $newName = 'batman';

        $document = new Document();
        $script = new Script(
            "ctx._source.name = name; ctx._source.counter += count",
            array(
                'name' => $newName,
                'count' => 2,
            ),
            null,
            $id
        );
        $script->setUpsert($document);

        $type->updateDocument($script, array('refresh' => true));
        $updatedDoc = $type->getDocument($id)->getData();
        $this->assertEquals($newName, $updatedDoc['name'], "Name was not updated");
        $this->assertEquals(3, $updatedDoc['counter'], "Counter was not incremented");
    }

    public function testUpdateDocumentWithFieldsSource()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_test');
        $type = $index->getType('update_type');

        $client->setConfigValue('document', array('autoPopulate' => true));

        $newDocument = new Document(null, array('counter' => 5, 'name' => 'Batman'));

        $this->assertFalse($newDocument->hasVersion());

        $response = $type->addDocument($newDocument);
        $responseData = $response->getData();

        $this->assertTrue($newDocument->hasVersion());
        $this->assertArrayHasKey('_version', $responseData, '_version is missing in response data it is weird');
        $this->assertEquals(1, $responseData['_version']);
        $this->assertEquals($responseData['_version'], $newDocument->getVersion());

        $this->assertTrue($newDocument->hasId());

        $script = new Script('ctx._source.counter += count; ctx._source.realName = realName');
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
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testUpdateDocumentWithoutId()
    {
        $index = $this->_createIndex();
        $type = $index->getType('elastica_type');

        $document = new Document();

        $type->updateDocument($document);
    }

    public function testUpdateDocumentWithoutSource()
    {
        $index = $this->_createIndex();
        $type = $index->getType('elastica_type');

        $mapping = new Mapping();
        $mapping->setProperties(array(
            'name' => array(
                'type' => 'string',
                'store' => 'yes'),
            'counter' => array(
                'type' => 'integer',
                'store' => 'no'
            ),
        ));
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
            $this->assertContains('DocumentSourceMissingException', $e->getMessage());
        }

        $newDocument->setFieldsSource();

        try {
            $type->updateDocument($newDocument);
            $this->fail('Update request should fail because source is disabled. Fields param is set to _source');
        } catch (ResponseException $e) {
            $this->assertContains('DocumentSourceMissingException', $e->getMessage());
        }
    }

    public function testAddDocumentHashId()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test2');

        $hashId = '#1';

        $doc = new Document($hashId, array('name' => 'ruflin'));
        $type->addDocument($doc);

        $index->refresh();

        $search = new Search($index->getClient());
        $search->addIndex($index);
        $resultSet = $search->search(new MatchAll());
        $this->assertEquals($hashId, $resultSet->current()->getId());

        $doc = $type->getDocument($hashId);
        $this->assertEquals($hashId, $doc->getId());
    }

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
        $this->assertInstanceOf('Elastica\Document', $foundDoc);
        $this->assertEquals($document->getId(), $foundDoc->getId());
        $data = $foundDoc->getData();
        $this->assertArrayHasKey('name', $data);
        $this->assertEquals('ruflin', $data['name']);
    }

    /**
     * @expectedException \Elastica\Exception\RuntimeException
     */
    public function testAddDocumentWithoutSerializer()
    {
        $index = $this->_createIndex();

        $type = new Type($index, 'user');

        $type->addObject(new \stdClass());
    }

    public function testAddObject()
    {
        $index = $this->_createIndex();

        $type = new Type($index, 'user');
        $type->setSerializer(array(new SerializerMock(), 'serialize'));

        $userObject = new \stdClass();
        $userObject->username = 'hans';
        $userObject->test = array('2', '3', '5');

        $type->addObject($userObject);

        $index->refresh();

        $resultSet = $type->search('hans');
        $this->assertEquals(1, $resultSet->count());

        // Test if source is returned
        $result = $resultSet->current();
        $data = $result->getData();
        $this->assertEquals('hans', $data['username']);
    }

    public function testExists()
    {
        $index = $this->_createIndex();
        $this->assertTrue($index->exists());

        $type = new Type($index, 'user');
        $this->assertFalse($type->exists());

        $type->addDocument(new Document(1, array('name' => 'test name')));
        $index->optimize();

        //Test if type exists
        $this->assertTrue($type->exists());

        $index->delete();
        $this->assertFalse($index->exists());
    }

    public function testGetMapping() {
        $indexName = 'test';
        $typeName = 'test-type';

        $index = $this->_createIndex($indexName);
        $indexName = $index->getName();
        $type = new Type($index, $typeName);
        $mapping = new Mapping($type, $expect = array(
            'id' => array('type' => 'integer', 'store' => true)
        ));
        $type->setMapping($mapping);

        $client = $index->getClient();

        $this->assertEquals(
            array('test-type' => array('properties' => $expect)),
            $client->getIndex($indexName)->getType($typeName)->getMapping()
        );
    }

    public function testGetMappingAlias() {
        $indexName = 'test';
        $aliasName = 'test-alias';
        $typeName = 'test-alias-type';

        $index = $this->_createIndex($indexName);
        $index->addAlias($aliasName);
        $type = new Type($index, $typeName);
        $mapping = new Mapping($type, $expect = array(
            'id' => array('type' => 'integer', 'store' => true)
        ));
        $type->setMapping($mapping);

        $client = $index->getClient();

        $this->assertEquals(
            array('test-alias-type' => array('properties' => $expect)),
            $client->getIndex($aliasName)->getType($typeName)->getMapping()
        );
    }
}

class SerializerMock
{
    public function serialize($object)
    {
        return get_object_vars($object);
    }
}
