<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';

class Elastica_ClientTest extends Elastica_Test
{

    public function testConstruct()
    {
        $host = 'ruflin.com';
        $port = 9300;
        $client = new Elastica_Client(array('host' => $host, 'port' => $port));

        $this->assertEquals($host, $client->getHost());
        $this->assertEquals($port, $client->getPort());
    }

    public function testDefaults()
    {
        $client = new Elastica_Client();

        $this->assertEquals(Elastica_Client::DEFAULT_HOST, 'localhost');
        $this->assertEquals(Elastica_Client::DEFAULT_PORT, 9200);
        $this->assertEquals(Elastica_Client::DEFAULT_TRANSPORT, 'Http');

        $this->assertEquals(Elastica_Client::DEFAULT_HOST, $client->getHost());
        $this->assertEquals(Elastica_Client::DEFAULT_PORT, $client->getPort());
        $this->assertEquals(Elastica_Client::DEFAULT_TRANSPORT, $client->getTransport());
    }

    public function testServersArray()
    {
        // Creates a new index 'xodoa' and a type 'user' inside this index
        $client = new Elastica_Client(array('servers' => array(array('host' => 'localhost', 'port' => 9200))));
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
        $client = new Elastica_Client(array('servers' => array(
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
}
