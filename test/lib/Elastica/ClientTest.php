<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_ClientTest extends Elastica_Test
{

    public function testConstruct()
    {
        $client = new Elastica_Client();
		$this->assertCount(1, $client->getConnections());
    }

    public function testConnectionsArray()
    {
        // Creates a new index 'xodoa' and a type 'user' inside this index
        $client = new Elastica_Client(array('connections' => array(array('host' => 'localhost', 'port' => 9200))));
        $index = $client->getIndex('elastica_test1');
        $index->create(array(), true);

        $type = $index->getType('user');

        // Adds 1 document to the index
        $doc1 = new Elastica_Document(1,
            array('username' => 'hans', 'test' => array('2', '3', '5'))
        );
        $type->addDocument($doc1);

        // Adds a list of documents with _bulk upload to the index
        $docs = array();
        $docs[] = new Elastica_Document(2,
            array('username' => 'john', 'test' => array('1', '3', '6'))
        );
        $docs[] = new Elastica_Document(3,
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
        $client = new Elastica_Client(array('connections' => array(
            array('host' => 'localhost', 'port' => 9200),
            array('host' => 'localhost', 'port' => 9200),
        )));
        $index = $client->getIndex('elastica_test1');
        $index->create(array(), true);

        $type = $index->getType('user');

        // Adds 1 document to the index
        $doc1 = new Elastica_Document(1,
        array('username' => 'hans', 'test' => array('2', '3', '5'))
        );
        $type->addDocument($doc1);

        // Adds a list of documents with _bulk upload to the index
        $docs = array();
        $docs[] = new Elastica_Document(2,
        array('username' => 'john', 'test' => array('1', '3', '6'))
        );
        $docs[] = new Elastica_Document(3,
        array('username' => 'rolf', 'test' => array('2', '3', '7'))
        );
        $type->addDocuments($docs);

        // Refresh index
        $index->refresh();

        $resultSet = $type->search('rolf');
    }

    public function testBulk()
    {
        $client = new Elastica_Client();

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
        $client = new Elastica_Client();
        $response = $client->optimizeAll();

        $this->assertFalse($response->hasError());
    }

    /**
     * @expectedException Elastica_Exception_Invalid
     */
    public function testAddDocumentsEmpty()
    {
        $client = new Elastica_Client();
        $client->addDocuments(array());
    }

    /**
    * Test deleteIds method using string parameters
    *
    * This test ensures that the deleteIds method of
    * the Elastica_Client can properly accept and use
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
        $doc = new Elastica_Document(null, $data);
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
        // are Elastica_Index and Elastica_Type objects respectively
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
    * the Elastica_Client can properly accept and use
    * an $index parameter that is a string and a $type
    * parameter that is of type Elastica_Type
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
        $doc = new Elastica_Document(null, $data);
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
        $this->assertEquals(true, ($type instanceof Elastica_Type));

        // Using the existing $index and $type variables which
        // are Elastica_Index and Elastica_Type objects respectively
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
    * the Elastica_Client can properly accept and use
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
        $doc = new Elastica_Document(null, $data);
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
        $this->assertEquals(true, ($index instanceof Elastica_Index));
        $this->assertEquals(true, is_string($typeString));

        // Using the existing $index and $type variables which
        // are Elastica_Index and Elastica_Type objects respectively
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
    * the Elastica_Client can properly accept and use
    * an $index parameter that is an object and a $type
    * parameter that is of type Elastica_Type
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
        $doc = new Elastica_Document(null, $data);
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
        $this->assertEquals(true, ($index instanceof Elastica_Index));
        $this->assertEquals(true, ($type instanceof Elastica_Type));

        // Using the existing $index and $type variables which
        // are Elastica_Index and Elastica_Type objects respectively
        $resp = $index->getClient()->deleteIds($ids, $index, $type);

        // Refresh the index to clear out deleted ID information
        $index->refresh();

        // Research the index to verify that the items have been deleted
        $resultSet = $type->search($userSearch);
        $totalHits = $resultSet->getTotalHits();
        $this->assertEquals(0, $totalHits);
    }

	public function testOneInvalidConnection() {
		$client = new Elastica_Client();

		// First connection work, second should not work
		$connection1 = new Elastica_Connection(array('host' => '127.0.0.1', 'timeout' => 2));
		$connection2 = new Elastica_Connection(array('host' => '127.0.0.2', 'timeout' => 2));

		$client->setConnections(array($connection1, $connection2));

		$client->request('_status', Elastica_Request::GET);

		$connections = $client->getConnections();

		// two connections are setup
		$this->assertEquals(2, count($connections));

		// One connection has to be disabled
		$this->assertTrue($connections[0]->isEnabled() == false || $connections[1]->isEnabled() == false);
	}

	public function testTwoInvalidConnection() {
		$client = new Elastica_Client();

		// First connection work, second should not work
		$connection1 = new Elastica_Connection(array('host' => '127.0.0.2', 'timeout' => 2));
		$connection2 = new Elastica_Connection(array('host' => '127.0.0.3', 'timeout' => 2));

		$client->setConnections(array($connection1, $connection2));

		try {
			$client->request('_status', Elastica_Request::GET);
			$this->fail('Should throw exception as no connection valid');
		} catch(Elastica_Exception_Client $e) {
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
	public function testCallback() {

		$count = 0;
		$object = $this;

		// Callback function which verifies that disabled connection objects are returned
		$callback = function($connection) use (&$object, &$count) {
			$object->assertInstanceOf('Elastica_Connection', $connection);
			$object->assertFalse($connection->isEnabled());
			$count++;
		};

		$client = new Elastica_Client(array(), $callback);

		// First connection work, second should not work
		$connection1 = new Elastica_Connection(array('host' => '127.0.0.2', 'timeout' => 2));
		$connection2 = new Elastica_Connection(array('host' => '127.0.0.3', 'timeout' => 2));

		$client->setConnections(array($connection1, $connection2));

		$this->assertEquals(0, $count);

		try {
			$client->request('_status', Elastica_Request::GET);
			$this->fail('Should throw exception as no connection valid');
		} catch(Elastica_Exception_Client $e) {
			$this->assertTrue(true);
		}

		// Two disabled connections (from closure call)
		$this->assertEquals(2, $count);
	}
}
