<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class ElasticSearch_Query_BoolTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {     
    }
    
    public function tearDown() {
    }
    
    public function testSearch() {
    	$client = new ElasticSearch_Client();    
    	$index = new ElasticSearch_Index($client, 'test');
    	$index->create(array(), true);
    	
    	$type = new ElasticSearch_Type($index, 'helloworld');

    	$doc = new ElasticSearch_Document(1, array('id' => 1, 'email' => 'hans@test.com', 'username' => 'hans', 'test' => array('2', '3', '5')));
    	$type->addDocument($doc);
    	$doc = new ElasticSearch_Document(2, array('id' => 2, 'email' => 'emil@test.com', 'username' => 'emil', 'test' => array('1', '3', '6')));
    	$type->addDocument($doc);
    	$doc = new ElasticSearch_Document(3, array('id' => 3, 'email' => 'ruth@test.com', 'username' => 'ruth', 'test' => array('2', '3', '7')));
    	$type->addDocument($doc);
    	
    	// Needs some time to write to index
    	sleep(1);
    	
    	$boolQuery = new ElasticSearch_Query_Bool();
    	$termQuery1 = new ElasticSearch_Query_Term(array('test' => '2'));
    	$boolQuery->addMust($termQuery1);
    	$resultSet = $type->search($boolQuery);
        
        $this->assertEquals(2, $resultSet->count());

    	$termQuery2 = new ElasticSearch_Query_Term(array('test' => '5'));
    	$boolQuery->addMust($termQuery2);
    	$resultSet = $type->search($boolQuery);
    	
    	$this->assertEquals(1, $resultSet->count());
    	
    	$termQuery3 = new ElasticSearch_Query_Term(array('username' => 'hans'));
    	$boolQuery->addMust($termQuery3);
    	$resultSet = $type->search($boolQuery);
    	
    	$this->assertEquals(1, $resultSet->count());
    	
    	$termQuery4 = new ElasticSearch_Query_Term(array('username' => 'emil'));
    	$boolQuery->addMust($termQuery4);
    	$resultSet = $type->search($boolQuery);
    	
    	$this->assertEquals(0, $resultSet->count());
    }
}
