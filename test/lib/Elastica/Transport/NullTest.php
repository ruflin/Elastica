<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Transport_NullTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
    	;
    }

    public function testConstruct()
    {
        $host = 'nullhost';
        $port = 0;
        $client = new Elastica_Client(array('host' => $host, 'port' => $port, 'transport' => 'Null'));

        $this->assertEquals($host, $client->getHost());
        $this->assertEquals($port, $client->getPort());
    }

    public function testEmptyResult()
    {
        // Creates a client with any destination, and verify it returns a response object when executed
        $host = 'nullhost';
        $port = 0;
        $client = new Elastica_Client(array('host' => $host, 'port' => $port, 'transport' => 'Null'));
    	
        $index = $client->getIndex('elasticaNullTransportTest1');

 		$response = $index->search("florian");
        $this->assertNotNull($response);
        
    }
}
