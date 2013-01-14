<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Connection;
use Elastica\Document;
use Elastica\Exception\ClientException;
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
        $connection1 = new Connection(array('port' => '9200', 'timeout' => 2));
        $connection2 = new Connection(array('port' => '9100', 'timeout' => 2));

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
        $callback = function($connection) use (&$object, &$count) {
            $object->assertInstanceOf('Elastica\Connection', $connection);
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
}
