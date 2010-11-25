<?php

require_once dirname(__FILE__) . '/../../bootstrap.php';


class Elastica_IndexTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        
    }
    
    public function tearDown() {
    }
    
    public function testTest() {
    	/*$client = new Elastica_Client();
    	    
    	$index = $client->getIndex('aaa');
    	$index->delete();
    	$index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 1)));
    	
    	$doc = new Elastica_Document('user', 1, array('id' => 1, 'email' => 'test@test.com', 'username' => 'hanswurst', 'test' => array('2', '3', '5')));
    	
    	$index->addDocument($doc);
    	$index->optimize();
		
    	
		$index->setMapping('user', 
			array(
				'id' => array('type' => 'integer', 'store' => 'yes'),
				'email' => array('type' => 'string', 'store' => 'no'),
				'username' => array('type' => 'string', 'store' => 'no'),
			)
		);
		
		print_r($index->getMapping('user'));*/
		
		//print_r($index->search('user', array('query' => 'hanswurst')));

    }
    
    public function testAddPDFFile() {
		$this->markTestIncomplete();
        $indexMapping = array(
        	'file' => array('type' => 'attachment'),
    		'text' => array('type' => 'string', 'store' => 'no'),
        );
        
        $indexParams = array(
    	    'index' => array(
    	        'number_of_shards' => 1,
    	        'number_of_replicas' => 1
    	    ),
    	);
        	   
        $client = new Elastica_Client();
        $index = new Elastica_Index($client, 'content');
        $type = new Elastica_Type($index, 'content');
        
        $index->create($indexParams, true);
        $type->setMapping($indexMapping);

        $doc1 = new Elastica_Document(1);
        $doc1->addFile('file', BASE_PATH . '/data/test.pdf');
        $doc1->add('text', 'basel world');
        $type->addDocument($doc1);
        
        $doc2 = new Elastica_Document(2);
        $doc2->add('text', 'running in basel');
        $type->addDocument($doc2);
        
        $index->optimize();        
        
        $resultSet = $type->search('xodoa');
        $this->assertEquals(1, $resultSet->count());
        
        $resultSet = $type->search('basel');
        $this->assertEquals(2, $resultSet->count());
        
        $resultSet = $type->search('ruflin');
        $this->assertEquals(0, $resultSet->count());
    }
    
    public function testAddWordxFile() {
        $this->markTestIncomplete();
        $indexMapping = array(
        	'file' => array('type' => 'attachment'),
    		'text' => array('type' => 'string', 'store' => 'no'),
        );
        
        $indexParams = array(
    	    'index' => array(
    	        'number_of_shards' => 1,
    	        'number_of_replicas' => 1
    	    ),
    	);
        	   
        $client = new Elastica_Client();
        $index = new Elastica_Index($client, 'content');
        $type = new Elastica_Type($index, 'content');
        
        $index->create($indexParams, true);
        $type->setMapping($indexMapping);

        $doc1 = new Elastica_Document(1);
        $doc1->addFile('file', BASE_PATH . '/data/test.docx');
        $doc1->add('text', 'basel world');
        $type->addDocument($doc1);
        
        $doc2 = new Elastica_Document(2);
        $doc2->add('text', 'running in basel');
        $type->addDocument($doc2);
        
        $index->optimize();        
        
        $resultSet = $type->search('xodoa');
        $this->assertEquals(1, $resultSet->count());
        
        $resultSet = $type->search('basel');
        $this->assertEquals(2, $resultSet->count());
        
        $resultSet = $type->search('ruflin');
        $this->assertEquals(0, $resultSet->count());
    }
}
