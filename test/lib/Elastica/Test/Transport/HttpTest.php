<?php

namespace Elastica\Test\Transport;

use Elastica\Client;
use Elastica\Document;
use Elastica\Test\Base as BaseTest;
use Elastica\Exception\ResponseException;

class HttpTest extends BaseTest
{
    public function setUp()
    {
        if (defined('DEBUG') && !DEBUG) {
            $this->markTestSkipped('The DEBUG constant must be set to true for this test to run');
        }

        if (!defined('DEBUG')) {
            define('DEBUG', true);
        }
    }

    /**
     * Return transport configuration and the expected HTTP method
     *
     * @return array[]
     */
    public function getConfig()
    {
        return array(
            array(
                array('transport' => 'Http'),
                'GET'
            ),
            array(
                array('transport' => array('type' => 'Http', 'postWithRequestBody' => false)),
                'GET'
            ),
            array(
                array('transport' => array('type' => 'Http', 'postWithRequestBody' => true)),
                'POST'
            ),
        );
    }

    /**
     * @dataProvider getConfig
     */
    public function testDynamicHttpMethodBasedOnConfigParameter(array $config, $httpMethod)
    {
        $client = new Client($config);

        $index = $client->getIndex('dynamic_http_method_test');

        $index->create(array(), true);

        $type = $index->getType('test');
        $type->addDocument(new Document(1, array('test' => 'test')));

        $index->refresh();

        $resultSet = $index->search('test');

        $info = $resultSet->getResponse()->getTransferInfo();
        $this->assertStringStartsWith($httpMethod, $info['request_header']);
    }

    /**
     * @dataProvider getConfig
     */
    public function testDynamicHttpMethodOnlyAffectsRequestsWithBody(array $config, $httpMethod)
    {
        $client = new Client($config);

        $status = $client->getStatus();
        $info = $status->getResponse()->getTransferInfo();
        $this->assertStringStartsWith('GET', $info['request_header']);
    }

    public function testCurlNobodyOptionIsResetAfterHeadRequest()
    {
        $client = new \Elastica\Client();
        $index = $client->getIndex('curl_test');
        $type = $index->getType('item');

        // Force HEAD request to set CURLOPT_NOBODY = true
        $index->exists();

        $id = 1;
        $data = array('id' => $id, 'name' => 'Item 1');
        $doc = new \Elastica\Document($id, $data);

        $type->addDocument($doc);

        $index->refresh();

        $doc = $type->getDocument($id);

        // Document should be retrieved correctly
        $this->assertSame($data, $doc->getData());
        $this->assertEquals($id, $doc->getId());
    }
}
