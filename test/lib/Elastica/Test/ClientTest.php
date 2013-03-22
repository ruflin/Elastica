<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Connection;
use Elastica\Document;
use Elastica\Exception\ClientException;
use Elastica\Script;
use Elastica\Index;
use Elastica\Request;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;

class ClientTest extends BaseTest
{

    public function testConstruct()
    {
        $client = $this->_getClient();
        $this->assertCount(1, $client->getConnections());
    }

    public function testConnectionsArray()
    {
        // Creates a new index 'xodoa' and a type 'user' inside this index
        $client = new Client(array('connections' => array(array('host' => 'localhost', 'port' => 9200))));
        $index = $client->getIndex('elastica_test1');
        $index->create(array(), true);

        $type = $index->getType('user');

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

        // Refresh index
        $index->refresh();

        $resultSet = $type->search('rolf');
    }

    public function testTwoServersSame()
    {
        // Creates a new index 'xodoa' and a type 'user' inside this index
        $client = new Client(array('connections' => array(
            array('host' => 'localhost', 'port' => 9200),
            array('host' => 'localhost', 'port' => 9200),
        )));
        $index = $client->getIndex('elastica_test1');
        $index->create(array(), true);

        $type = $index->getType('user');

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

        // Refresh index
        $index->refresh();

        $resultSet = $type->search('rolf');
    }

    public function testBulk()
    {
        $client = $this->_getClient();

        $params = array(
            array('index' => array('_index' => 'test', '_type' => 'user', '_id' => '1')),
            array('user' => array('name' => 'hans')),
            array('index' => array('_index' => 'test', '_type' => 'user', '_id' => '2')),
            array('user' => array('name' => 'peter')),
        );

        $client->bulk($params);
    }

    public function testOptimizeAll()
    {
        $client = $this->_getClient();
        $response = $client->optimizeAll();

        $this->assertFalse($response->hasError());
    }

    /**
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testAddDocumentsEmpty()
    {
        $client = $this->_getClient();
        $client->addDocuments(array());
    }

    /**
    * Test deleteIds method using string parameters
    *
    * This test ensures that the deleteIds method of
    * the \Elastica\Client can properly accept and use
    * an $index parameter and $type parameter that are
    * strings
    *
    * This test is a bit more verbose than just sending the
    * values to deleteIds and checking for exceptions or
    * warnings.
    *
    * It will add a document, search for it, then delete it
    * using the parameter types we are interested in, and then
    * re-search to verify that they have been deleted
    */
    public function testDeleteIdsIdxStringTypeString()
    {
        $data = array('username' => 'hans');
        $userSearch = 'username:hans';

        $index = $this->_createIndex();

        // Create the index, deleting it first if it already exists
        $index->create(array(), true);
        $type = $index->getType('user');

        // Adds 1 document to the index
        $doc = new Document(null, $data);
        $result = $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $resultData = $result->getData();
        $ids = array($resultData['_id']);

        // Check to make sure the document is in the index
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(1, $totalHits);

        // And verify that the variables we are doing to send to
        // deleteIds are the type we are testing for
        $idxString = $index->getName();
        $typeString = $type->getName();
        $this->assertEquals(true, is_string($idxString));
        $this->assertEquals(true, is_string($typeString));

        // Using the existing $index and $type variables which
        // are \Elastica\Index and \Elastica\Type objects respectively
        $resp = $index->getClient()->deleteIds($ids, $index, $type);

        // Refresh the index to clear out deleted ID information
        $index->refresh();

        // Research the index to verify that the items have been deleted
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(0, $totalHits);
    }

    /**
    * Test deleteIds method using string parameter for $index
    * and object parameter for $type
    *
    * This test ensures that the deleteIds method of
    * the \Elastica\Client can properly accept and use
    * an $index parameter that is a string and a $type
    * parameter that is of type \Elastica\Type
    *
    * This test is a bit more verbose than just sending the
    * values to deleteIds and checking for exceptions or
    * warnings.
    *
    * It will add a document, search for it, then delete it
    * using the parameter types we are interested in, and then
    * re-search to verify that they have been deleted
    */
    public function testDeleteIdsIdxStringTypeObject()
    {
        $data = array('username' => 'hans');
        $userSearch = 'username:hans';

        $index = $this->_createIndex();

        // Create the index, deleting it first if it already exists
        $index->create(array(), true);
        $type = $index->getType('user');

        // Adds 1 document to the index
        $doc = new Document(null, $data);
        $result = $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $resultData = $result->getData();
        $ids = array($resultData['_id']);

        // Check to make sure the document is in the index
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(1, $totalHits);

        // And verify that the variables we are doing to send to
        // deleteIds are the type we are testing for
        $idxString = $index->getName();
        $this->assertEquals(true, is_string($idxString));
        $this->assertEquals(true, ($type instanceof Type));

        // Using the existing $index and $type variables which
        // are \Elastica\Index and \Elastica\Type objects respectively
        $resp = $index->getClient()->deleteIds($ids, $index, $type);

        // Refresh the index to clear out deleted ID information
        $index->refresh();

        // Research the index to verify that the items have been deleted
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(0, $totalHits);
    }

    /**
    * Test deleteIds method using object parameter for $index
    * and string parameter for $type
    *
    * This test ensures that the deleteIds method of
    * the \Elastica\Client can properly accept and use
    * an $index parameter that is  of type Elasitca_Index
    * and a $type parameter that is a string
    *
    * This test is a bit more verbose than just sending the
    * values to deleteIds and checking for exceptions or
    * warnings.
    *
    * It will add a document, search for it, then delete it
    * using the parameter types we are interested in, and then
    * re-search to verify that they have been deleted
    */
    public function testDeleteIdsIdxObjectTypeString()
    {
        $data = array('username' => 'hans');
        $userSearch = 'username:hans';

        $index = $this->_createIndex();

        // Create the index, deleting it first if it already exists
        $index->create(array(), true);
        $type = $index->getType('user');

        // Adds 1 document to the index
        $doc = new Document(null, $data);
        $result = $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $resultData = $result->getData();
        $ids = array($resultData['_id']);

        // Check to make sure the document is in the index
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(1, $totalHits);

        // And verify that the variables we are doing to send to
        // deleteIds are the type we are testing for
        $typeString = $type->getName();
        $this->assertEquals(true, ($index instanceof Index));
        $this->assertEquals(true, is_string($typeString));

        // Using the existing $index and $type variables which
        // are \Elastica\Index and \Elastica\Type objects respectively
        $resp = $index->getClient()->deleteIds($ids, $index, $type);

        // Refresh the index to clear out deleted ID information
        $index->refresh();

        // Research the index to verify that the items have been deleted
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(0, $totalHits);
    }

    /**
    * Test deleteIds method using object parameter for $index
    * and object parameter for $type
    *
    * This test ensures that the deleteIds method of
    * the \Elastica\Client can properly accept and use
    * an $index parameter that is an object and a $type
    * parameter that is of type \Elastica\Type
    *
    * This test is a bit more verbose than just sending the
    * values to deleteIds and checking for exceptions or
    * warnings.
    *
    * It will add a document, search for it, then delete it
    * using the parameter types we are interested in, and then
    * re-search to verify that they have been deleted
    */
    public function testDeleteIdsIdxObjectTypeObject()
    {
        $data = array('username' => 'hans');
        $userSearch = 'username:hans';

        $index = $this->_createIndex();

        // Create the index, deleting it first if it already exists
        $index->create(array(), true);
        $type = $index->getType('user');

        // Adds 1 document to the index
        $doc = new Document(null, $data);
        $result = $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $resultData = $result->getData();
        $ids = array($resultData['_id']);

        // Check to make sure the document is in the index
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(1, $totalHits);

        // And verify that the variables we are doing to send to
        // deleteIds are the type we are testing for
        $this->assertEquals(true, ($index instanceof Index));
        $this->assertEquals(true, ($type instanceof Type));

        // Using the existing $index and $type variables which
        // are \Elastica\Index and \Elastica\Type objects respectively
        $resp = $index->getClient()->deleteIds($ids, $index, $type);

        // Refresh the index to clear out deleted ID information
        $index->refresh();

        // Research the index to verify that the items have been deleted
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(0, $totalHits);
    }

    public function testOneInvalidConnection()
    {
        $client = $this->_getClient();

        // First connection work, second should not work
        $connection1 = new Connection(array('port' => '9100', 'timeout' => 2));
        $connection2 = new Connection(array('port' => '9200', 'timeout' => 2));

        $client->setConnections(array($connection1, $connection2));

        $client->request('_status', Request::GET);

        $connections = $client->getConnections();

        // two connections are setup
        $this->assertEquals(2, count($connections));

        // One connection has to be disabled
        $this->assertTrue($connections[0]->isEnabled() == false || $connections[1]->isEnabled() == false);
    }

    public function testTwoInvalidConnection()
    {
        $client = $this->_getClient();

        // First connection work, second should not work
        $connection1 = new Connection(array('port' => '9101', 'timeout' => 2));
        $connection2 = new Connection(array('port' => '9102', 'timeout' => 2));

        $client->setConnections(array($connection1, $connection2));

        try {
            $client->request('_status', Request::GET);
            $this->fail('Should throw exception as no connection valid');
        } catch (ClientException $e) {
            $this->assertTrue(true);
        }

        $connections = $client->getConnections();

        // two connections are setup
        $this->assertEquals(2, count($connections));

        // One connection has to be disabled
        $this->assertTrue($connections[0]->isEnabled() == false || $connections[1]->isEnabled() == false);
    }

    /**
     * Tests if the callback works in case a connection is down
     */
    public function testCallback()
    {
        $count = 0;
        $object = $this;

        // Callback function which verifies that disabled connection objects are returned
        $callback = function($connection, $exception) use (&$object, &$count) {
            $object->assertInstanceOf('Elastica\Connection', $connection);
            $object->assertInstanceOf('Elastica\Exception\ConnectionException', $exception);
            $object->assertFalse($connection->isEnabled());
            $count++;
        };

        $client = new Client(array(), $callback);

        // First connection work, second should not work
        $connection1 = new Connection(array('port' => '9101', 'timeout' => 2));
        $connection2 = new Connection(array('port' => '9102', 'timeout' => 2));

        $client->setConnections(array($connection1, $connection2));

        $this->assertEquals(0, $count);

        try {
            $client->request('_status', Request::GET);
            $this->fail('Should throw exception as no connection valid');
        } catch (ClientException $e) {
            $this->assertTrue(true);
        }

        // Two disabled connections (from closure call)
        $this->assertEquals(2, $count);
    }

    public function testUrlConstructor()
    {
        $url = 'http://localhost:9200/';

        // Url should overwrite invalid host
        $client = new Client(array('url' => $url, 'port' => '9101', 'timeout' => 2));

        $response = $client->request('_status');
        $this->assertInstanceOf('Elastica\Response', $response);
    }

    public function testUpdateDocumentByDocument()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');
        $client = $index->getClient();

        $newDocument = new Document(1, array('field1' => 'value1', 'field2' => 'value2'));
        $type->addDocument($newDocument);

        $updateDocument = new Document(1, array('field2' => 'value2changed', 'field3' => 'value3added'));
        $client->updateDocument(1, $updateDocument, $index->getName(), $type->getName());

        $document = $type->getDocument(1);

        $this->assertInstanceOf('Elastica\Document', $document);
        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('value2changed', $data['field2']);
        $this->assertArrayHasKey('field3', $data);
        $this->assertEquals('value3added', $data['field3']);
    }

    public function testUpdateDocumentByScript()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');
        $client = $index->getClient();

        $newDocument = new Document(1, array('field1' => 'value1', 'field2' => 10, 'field3' => 'should be removed', 'field4' => 'should be changed'));
        $type->addDocument($newDocument);

        $script = new Script('ctx._source.field2 += 5; ctx._source.remove("field3"); ctx._source.field4 = "changed"');
        $client->updateDocument(1, $script, $index->getName(), $type->getName());

        $document = $type->getDocument(1);

        $this->assertInstanceOf('Elastica\Document', $document);
        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals(15, $data['field2']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('changed', $data['field4']);
        $this->assertArrayNotHasKey('field3', $data);
    }

    public function testUpdateDocumentByDocumentWithScript()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');
        $client = $index->getClient();

        $newDocument = new Document(1, array('field1' => 'value1', 'field2' => 10, 'field3' => 'should be removed', 'field4' => 'value4'));
        $script = new Script('ctx._source.field2 += count; ctx._source.remove("field3"); ctx._source.field4 = "changed"');
        $script->setParam('count', 5);
        $newDocument->setScript($script);

        // should use document fields because document does not exist, script is avoided
        $client->updateDocument(1, $newDocument, $index->getName(), $type->getName(), array('fields' => '_source'));

        $document = $type->getDocument(1);

        $this->assertInstanceOf('Elastica\Document', $document);
        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals(10, $data['field2']);
        $this->assertArrayHasKey('field3', $data);
        $this->assertEquals('should be removed', $data['field3']);
        $this->assertArrayHasKey('field4', $data);
        $this->assertEquals('value4', $data['field4']);

        // should use script because document exists, document values are ignored
        $client->updateDocument(1, $newDocument, $index->getName(), $type->getName(), array('fields' => '_source'));

        $document = $type->getDocument(1);

        $this->assertInstanceOf('Elastica\Document', $document);
        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals(15, $data['field2']);
        $this->assertArrayHasKey('field4', $data);
        $this->assertEquals('changed', $data['field4']);
        $this->assertArrayNotHasKey('field3', $data);
    }

    public function testUpdateDocumentByRawData()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');
        $client = $index->getClient();

        $newDocument = new Document(1, array('field1' => 'value1'));
        $type->addDocument($newDocument);

        $rawData = array(
            'doc' => array(
                'field2' => 'value2',
            )
        );

        $response = $client->updateDocument(1, $rawData, $index->getName(), $type->getName(), array('retry_on_conflict' => 1));
        $this->assertTrue($response->isOk());

        $document = $type->getDocument(1);

        $this->assertInstanceOf('Elastica\Document', $document);
        $data = $document->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals('value2', $data['field2']);
    }

    public function testDeleteDocuments()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');
        $client = $index->getClient();

        $docs = array(
            new Document(1, array('field' => 'value1'), $type, $index),
            new Document(2, array('field' => 'value2'), $type, $index),
            new Document(3, array('field' => 'value3'), $type, $index),
        );

        $response = $client->addDocuments($docs);

        $this->assertInstanceOf('Elastica\Bulk\ResponseSet', $response);
        $this->assertEquals(3, count($response));
        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());
        $this->assertEquals('', $response->getError());

        $index->refresh();

        $this->assertEquals(3, $type->count());

        $deleteDocs = array(
            $docs[0],
            $docs[2],
        );

        $response = $client->deleteDocuments($deleteDocs);

        $this->assertInstanceOf('Elastica\Bulk\ResponseSet', $response);
        $this->assertEquals(2, count($response));
        $this->assertTrue($response->isOk());
        $this->assertFalse($response->hasError());
        $this->assertEquals('', $response->getError());

        $index->refresh();

        $this->assertEquals(1, $type->count());
    }

    public function testLastRequestResponse()
    {
        $client = new Client(array('log' => '/tmp/php.log'));
        $response = $client->request('_status');

        $this->assertInstanceOf('Elastica\Response', $response);

        $lastRequest = $client->getLastRequest();

        $this->assertInstanceOf('Elastica\Request', $lastRequest);
        $this->assertEquals('_status', $lastRequest->getPath());

        $lastResponse = $client->getLastResponse();
        $this->assertInstanceOf('Elastica\Response', $lastResponse);
        $this->assertSame($response, $lastResponse);
    }

    public function testUpdateDocumentPopulateFields()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');
        $client = $index->getClient();

        $newDocument = new Document(1, array('field1' => 'value1', 'field2' => 10, 'field3' => 'should be removed', 'field4' => 'value4'));
        $newDocument->setAutoPopulate();
        $type->addDocument($newDocument);

        $script = new Script('ctx._source.field2 += count; ctx._source.remove("field3"); ctx._source.field4 = "changed"');
        $script->setParam('count', 5);
        $newDocument->setScript($script);

        $client->updateDocument(
            1,
            $newDocument,
            $index->getName(),
            $type->getName(),
            array('fields' => '_source')
        );

        $data = $newDocument->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals(15, $data['field2']);
        $this->assertArrayHasKey('field4', $data);
        $this->assertEquals('changed', $data['field4']);
        $this->assertArrayNotHasKey('field3', $data);

        $script = new Script('ctx._source.field2 += count; ctx._source.remove("field4"); ctx._source.field1 = field1;');
        $script->setParam('count', 5);
        $script->setParam('field1', 'updated');
        $newDocument->setScript($script);

        $client->updateDocument(
            1,
            $newDocument,
            $index->getName(),
            $type->getName(),
            array('fields' => 'field2,field4')
        );

        $data = $newDocument->getData();
        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('value1', $data['field1'], 'Field1 should not be updated, because it is not in fields list');
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals(20, $data['field2'], 'Field2 should be 20 after incrementing by 5');
        $this->assertArrayNotHasKey('field3', $data, 'Field3 should be removed already');
        $this->assertArrayNotHasKey('field4', $data, 'Field3 should be removed');

        $document = $type->getDocument(1);

        $data = $document->getData();

        $this->assertArrayHasKey('field1', $data);
        $this->assertEquals('updated', $data['field1']);
        $this->assertArrayHasKey('field2', $data);
        $this->assertEquals(20, $data['field2']);
        $this->assertArrayNotHasKey('field3', $data);
        $this->assertArrayNotHasKey('field4', $data);
    }

    public function testAddDocumentsWithoutIds()
    {
        $docs = array();
        for ($i = 0; $i < 10; $i++) {
            $docs[] = new Document(null, array('pos' => $i));
        }

        foreach ($docs as $doc) {
            $this->assertFalse($doc->hasId());
        }

        $index = $this->_createIndex();

        $client = $index->getClient();
        $client->setConfigValue('document', array('autoPopulate' => true));

        $type = $index->getType('pos');
        $type->addDocuments($docs);

        foreach ($docs as $doc) {
            $this->assertTrue($doc->hasId());
            $this->assertTrue($doc->hasVersion());
            $this->assertEquals(1, $doc->getVersion());
        }
    }

    public function testConfigValue()
    {
        $config = array(
            'level1' => array(
                'level2' => array(
                    'level3' => 'value3',
                ),
                'level21' => 'value21'
            ),
            'level11' => 'value11'
        );
        $client = new Client($config);

        $this->assertNull($client->getConfigValue('level12'));
        $this->assertFalse($client->getConfigValue('level12', false));
        $this->assertEquals(10, $client->getConfigValue('level12', 10));

        $this->assertEquals('value11', $client->getConfigValue('level11'));
        $this->assertNotNull($client->getConfigValue('level11'));
        $this->assertNotEquals(false, $client->getConfigValue('level11', false));
        $this->assertNotEquals(10, $client->getConfigValue('level11', 10));

        $this->assertEquals('value3', $client->getConfigValue(array('level1', 'level2', 'level3')));
        $this->assertInternalType('array', $client->getConfigValue(array('level1', 'level2')));
    }
}
