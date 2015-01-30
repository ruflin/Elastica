<?php

namespace Elastica\Test\Transport;

use Elastica\Client;
use Elastica\Document;
use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Test\Base as BaseTest;

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
                'GET',
            ),
            array(
                array('transport' => array('type' => 'Http', 'postWithRequestBody' => false)),
                'GET',
            ),
            array(
                array('transport' => array('type' => 'Http', 'postWithRequestBody' => true)),
                'POST',
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
        $this->_waitForAllocation($index);

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
        $index->create(array(), true);
        $this->_waitForAllocation($index);

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

    public function testUnicodeData()
    {
        $client = new \Elastica\Client();
        $index = $client->getIndex('curl_test');
        $index->create(array(), true);
        $this->_waitForAllocation($index);

        $type = $index->getType('item');

        // Force HEAD request to set CURLOPT_NOBODY = true
        $index->exists();

        $id = 22;
        $data = array('id' => $id, 'name' => '
            Сегодня, я вижу, особенно грустен твой взгляд, /
            И руки особенно тонки, колени обняв. /
            Послушай: далеко, далеко, на озере Чад /
            Изысканный бродит жираф.');

        $doc = new \Elastica\Document($id, $data);

        $type->addDocument($doc);

        $index->refresh();

        $doc = $type->getDocument($id);

        // Document should be retrieved correctly
        $this->assertSame($data, $doc->getData());
        $this->assertEquals($id, $doc->getId());
    }

    public function testWithEnvironmentalProxy()
    {
        putenv('http_proxy=http://127.0.0.1:12345/');

        $client = new \Elastica\Client();
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);

        $client->getConnection()->setProxy(null); // will not change anything
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);

        putenv('http_proxy=');
    }

    public function testWithEnabledEnvironmentalProxy()
    {
        putenv('http_proxy=http://127.0.0.1:12346/');

        $client = new \Elastica\Client();

        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(403, $transferInfo['http_code']);

        $client = new \Elastica\Client();
        $client->getConnection()->setProxy('');
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);

        putenv('http_proxy=');
    }

    public function testWithProxy()
    {
        $client = new \Elastica\Client();
        $client->getConnection()->setProxy('http://127.0.0.1:12345');

        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
    }

    public function testWithoutProxy()
    {
        $client = new \Elastica\Client();
        $client->getConnection()->setProxy('');

        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
    }

    public function testBodyReuse()
    {
        $client = new Client();

        $index = $client->getIndex('elastica_body_reuse_test');
        $index->create(array(), true);
        $this->_waitForAllocation($index);

        $type = $index->getType('test');
        $type->addDocument(new Document(1, array('test' => 'test')));

        $index->refresh();

        $resultSet = $index->search(array(
            'query' => array(
                'query_string' => array(
                    'query' => 'pew pew pew',
                ),
            ),
        ));

        $this->assertEquals(0, $resultSet->getTotalHits());

        $response = $index->request('/_search', 'POST');
        $resultSet = new ResultSet($response, Query::create(array()));

        $this->assertEquals(1, $resultSet->getTotalHits());
    }

    public function testPostWith0Body()
    {
        $client = new Client();

        $index = $client->getIndex('elastica_0_body');
        $index->create(array(), true);
        $this->_waitForAllocation($index);
        $index->refresh();

        $tokens = $index->analyze('0');

        $this->assertNotEmpty($tokens);
    }
}
