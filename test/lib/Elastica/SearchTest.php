<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';


class Elastica_SearchTest extends Elastica_Test
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testConstruct() {
		$client = new Elastica_Client();
		$search = new Elastica_Search($client);

		$this->assertInstanceOf('Elastica_Search', $search);
		$this->assertSame($client, $search->getClient());
	}

	public function testAddIndex() {
		$client = new Elastica_Client();
		$search = new Elastica_Search($client);

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
        $client = new Elastica_Client();
		$search = new Elastica_Search($client);

        $indices = array();
        $indices[] = $client->getIndex('elastica_test1');
        $indices[] = $client->getIndex('elastica_test2');

        $search->addIndices($indices);

        $this->assertEquals(2, count($search->getIndices()));
    }

	public function testAddType() {
		$client = new Elastica_Client();
		$search = new Elastica_Search($client);

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
        $client = new Elastica_Client();
		$search = new Elastica_Search($client);

		$index = $this->_createIndex();

        $types = array();
        $types[] = $index->getType('type1');
        $types[] = $index->getType('type2');

        $search->addTypes($types);

        $this->assertEquals(2, count($search->getTypes()));
    }

	public function testAddTypeInvalid() {
		$client = new Elastica_Client();
		$search = new Elastica_Search($client);

		try {
			$search->addType(new stdClass());
			$this->fail('Should throw invalid exception');
		} catch(Elastica_Exception_Invalid $e) {
			$this->assertTrue(true);
		}
	}

	public function testAddIndexInvalid() {
		$client = new Elastica_Client();
		$search = new Elastica_Search($client);

		try {
			$search->addIndex(new stdClass());
			$this->fail('Should throw invalid exception');
		} catch(Elastica_Exception_Invalid $e) {
			$this->assertTrue(true);
		}
	}

	public function testGetPath() {
		$client = new Elastica_Client();
		$search1 = new Elastica_Search($client);
		$search2 = new Elastica_Search($client);

		$index1 = $this->_createIndex('test1');
		$index2 = $this->_createIndex('test2');

		$type1 = $index1->getType('type1');
		$type2 = $index1->getType('type2');

		// No index
		$this->assertEquals('_all/_search', $search1->getPath());

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

	public function testSearchRequest() {
		$client = new Elastica_Client();
		$search1 = new Elastica_Search($client);

		
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

    /**
     * Default Limit tests for Elastica_Search
     */
    public function testLimitDefaultSearch()
    {
        $client = new Elastica_Client();
        $search = new Elastica_Search($client);

        $index = $client->getIndex('zero');
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);

        $docs = array();
        $docs[] = new Elastica_Document(1, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(2, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(3, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(4, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(5, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(6, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(7, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(8, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(9, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(10, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
        $docs[] = new Elastica_Document(11, array('id' => 1, 'email' => 'test@test.com', 'username' => 'farrelley'));
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


    public function testArrayConfigSearch(){
        $client = new Elastica_Client();
        $search = new Elastica_Search($client);

        $index = $client->getIndex('zero');
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);

        $docs = array();
        for ($i=0; $i < 11; $i++){
            $docs[] = new Elastica_Document($i, array('id' => 1, 'email' => 'test@test.com', 'username' => 'test'));
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
        $resultSet = $search->search('test',array('limit'=>2));
        $this->assertEquals(2, $resultSet->count());
        
        //Array with routing
        $resultSet = $search->search('test',array('routing'=>'r1,r2'));
        $this->assertEquals(10, $resultSet->count());
        
        //Array with limit and routing
        $resultSet = $search->search('test',array('limit'=>5,'routing'=>'r1,r2'));
        $this->assertEquals(5,$resultSet->count());
        
        //Search types
        $resultSet = $search->search('test',array('limit'=>5,'search_type'=>'count'));
        $this->assertTrue(($resultSet->count()===0) && $resultSet->getTotalHits()===11);
        
        //Invalid option
        try{
            $resultSet = $search->search('test',array('invalid_option'=>'invalid_option_value'));
            $this->fail('Should throw Elastica_Exception_Invalid');
        }catch(Exception $ex){
             $this->assertTrue($ex instanceof Elastica_Exception_Invalid);
        }
        
        //Invalid value
        try{
            $resultSet = $search->search('test',array('routing'=>null));
            $this->fail('Should throw Elastica_Exception_Invalid');
        }catch(Exception $ex){
             $this->assertTrue($ex instanceof Elastica_Exception_Invalid);
        }
    }
}