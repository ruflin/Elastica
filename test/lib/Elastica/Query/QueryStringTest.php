<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class ElasticSearch_Query_QueryStringTest extends PHPUnit_Framework_TestCase
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

    	$doc = new ElasticSearch_Document(1, array('id' => 1, 'email' => 'test@test.com', 'username' => 'hanswurst', 'test' => array('2', '3', '5')));
    	$type->addDocument($doc);
    	
    	$queryString = new ElasticSearch_Query_QueryString('test*');
    	
    	// Needs some time to write to index
    	sleep(1);
    	$query = new ElasticSearch_Query();
    	$query->addQuery($queryString);
    	
    	$resultSet = $type->search($query);
    	
    	$this->assertEquals(1, $resultSet->count());
    }
}
