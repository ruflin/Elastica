<?php

namespace Elastica\Test\Transport;

use Elastica\Connection;
use Elastica\Query;
use Elastica\Test\Base as BaseTest;
use Elastica\Transport\Null;

/**
 * Elastica Null Transport Test
 *
 * @package Elastica
 * @author James Boehmer <james.boehmer@jamesboehmer.com>
 */
class NullTest extends BaseTest
{

    public function testEmptyResult()
    {
        // Creates a client with any destination, and verify it returns a response object when executed
        $client = $this->_getClient();
        $connection = new Connection(array('transport' => 'Null'));
        $client->setConnections(array($connection));

        $index = $client->getIndex('elasticaNullTransportTest1');

         $resultSet = $index->search(new Query());
         $this->assertNotNull($resultSet);

         $response = $resultSet->getResponse();
         $this->assertNotNull($response);

         // Validate most of the expected fields in the response data.  Consumers of the response
         // object have a reasonable expectation of finding "hits", "took", etc
         $responseData = $response->getData();
         $this->assertContains("took", $responseData);
         $this->assertEquals(0, $responseData["took"]);
         $this->assertContains("_shards", $responseData);
         $this->assertContains("hits", $responseData);
         $this->assertContains("total", $responseData["hits"]);
         $this->assertEquals(0, $responseData["hits"]["total"]);
         $this->assertContains("params", $responseData);

         $took = $response->getEngineTime();
         $this->assertEquals(0, $took);

         $errorString = $response->getError();
         $this->assertEmpty($errorString);

         $shards = $response->getShardsStatistics();
         $this->assertContains("total", $shards);
         $this->assertEquals(0, $shards["total"]);
         $this->assertContains("successful", $shards);
         $this->assertEquals(0, $shards["successful"]);
         $this->assertContains("failed", $shards);
         $this->assertEquals(0, $shards["failed"]);
    }

    public function testUri()
    {
        $config = array(
            'host' => 'null.es.host',
            'port' => 9666,
        );
        $connection = new Connection($config);
        $transport = new Null($connection);

        $uri = $transport->getUri();
        $this->assertEquals('null://', $uri);
    }
}
