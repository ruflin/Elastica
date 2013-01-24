<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Document;
use Elastica\Query;
use Elastica\Query\MatchAll;
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

        // Test if source is returned
        $result = $resultSet->current();
        $this->assertEquals(3, $result->getId());
        $data = $result->getData();
        $this->assertEquals('rolf', $data['username']);
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

        // it should not be possible to delete the entire type with this method
        try {
            $type->deleteById(' ');
        } catch (\Exception $e) {
            /* ignore */
        }

        try {
            $type->deleteById(null);
        } catch (\Exception $e) {
            /* ignore */
        }

        try {
            $type->deleteById(array());
        } catch (\Exception $e) {
            /* ignore */
        }

        try {
            $type->deleteById('*');
        } catch (\Exception $e) {
            /* ignore */
        }

        try {
            $type->deleteById('*:*');
        } catch (\Exception $e) {
            /* ignore */
        }

        try {
            $type->deleteById('!');
        } catch (\Exception $e) {
            /* ignore */
        }

        $index->refresh();

        // rolf should no longer be there
        $resultSet = $type->search('john');
        $this->assertEquals(1, $resultSet->count());
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

    public function testDeleteByQuery()
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
     * @expectedException \Elastica\Exception\ResponseException
     * @expectedExceptionMessage TypeMissingException[[elastica_test] type[test] missing]
     */
    public function testDeleteType()
    {
        $index = $this->_createIndex();
        $type = new Type($index, 'test');
        $type->addDocument(new Document(1, array('name' => 'ruflin nicolas')));
        $type->addDocument(new Document(2, array('name' => 'ruflin')));
        $index->refresh();

        $type->delete();
        $type->getMapping();
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
        $query->setFilter($filterTerm);

        $resultSet = $type->moreLikeThis($document, array('min_term_freq' => '1', 'min_doc_freq' => '1'), $query);
        $this->assertEquals(2, $resultSet->count());
    }

    public function testUpdateDocument()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_test');
        $type = $index->getType('update_type');
        $id = 1;
        $type->addDocument(new Document($id, array('name' => 'bruce wayne batman')));
        $newName = 'batman';

        $document = new Document($id);
        $script = new Script("ctx._source.name = name", array('name' => $newName));
        $document->setScript($script);

        $type->updateDocument($document, array('refresh' => true));
        $updatedDoc = $type->getDocument($id)->getData();
        $this->assertEquals($newName, $updatedDoc['name'], "Name was not updated");
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

    public function testGetType()
    {
        $index = new Index($this->_getClient(), 'index');
        $type = $index->getType('type');
        $this->assertEquals('type', $type->getType());
    }

    /**
     * @expectedException Elastica_Exception_Runtime
     */
    public function testAddDocumentWithoutSerializer()
    {
        $index = $this->_createIndex();

        $type = new Elastica_Type($index, 'user');

        $type->addObject(new stdClass());
    }

    public function testAddObject()
    {
        $index = $this->_createIndex();

        $type = new Elastica_Type($index, 'user');
        $type->setSerializer(array(new SerializerMock(), 'serialize'));

        $userObject = new stdClass();
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
}

class SerializerMock
{
    public function serialize($object)
    {
        return get_object_vars($object);
    }
}
