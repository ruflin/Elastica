<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';


class ElasticSearch_TypeTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {     
    }
    
    public function tearDown() {
    }
    
    public function testTest() {
    	// Creates a new index 'xodoa' and a type 'user' inside this index
    	$client = new ElasticSearch_Client();    
    	$index = new ElasticSearch_Index($client, 'xodoa');
    	$index->create(array(), true);

    	$type = new ElasticSearch_Type($index, 'user');


    	// Adds 1 document to the index
    	$doc1 = new ElasticSearch_Document(1, 
    		array('username' => 'hans', 'test' => array('2', '3', '5'))
    	);
    	$type->addDocument($doc1);

    	// Adds a list of documents with _bulk upload to the index
    	$docs = array();
    	$docs[] = new ElasticSearch_Document(2, 
    		array('username' => 'john', 'test' => array('1', '3', '6'))
    	);
    	$docs[] = new ElasticSearch_Document(3, 
    		array('username' => 'rolf', 'test' => array('2', '3', '7'))
    	);
    	$type->addDocuments($docs);
    	
    	sleep(1);
    	
    	print_r($type->search('rolf'));

    }
    
    public function testSearchDefault() {
        
    }
}
