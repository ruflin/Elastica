<?php
namespace Elastica\Test\Transport;

use Elastica\Connection;
use Elastica\Query;
use Elastica\Request;
use Elastica\Response;
use Elastica\Test\Base as BaseTest;
use Elastica\Transport\ErrorTransport;

/**
 * Elastica Error Transport Test.
 *
 * @author Jan Domanski <jandom@gmail.com>
 */
class ErrorTransportTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testEmptyResult()
    {
        // Creates a client with any destination, and verify it returns a response object when executed
        $client = $this->_getClient();
        $connection = new Connection(['transport' => 'ErrorTransport']);
        $client->setConnections([$connection]);

        $index = $client->getIndex('elasticaErrorTransportTest1');

        $resultSet = $index->search(new Query());
        $this->assertNotNull($resultSet);

        $response = $resultSet->getResponse();
        $this->assertNotNull($response);

         // Validate most of the expected fields in the response data.  Consumers of the response
         // object have a reasonable expectation of finding "hits", "took", etc
         $responseData = $response->getData();
         $desiredMessage = 'The request signature we calculated does not match the signature you provided.';
         $this->assertArrayHasKey('message', $responseData);
         $this->assertEquals($responseData['message'], $desiredMessage);

         $transferInfo = $response->getTransferInfo();
         $this->assertArrayHasKey('http_code', $transferInfo);
         $this->assertEquals($transferInfo['http_code'], 403);
    }
}
