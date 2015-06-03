<?php
namespace Elastica\Test\Transport;

use Elastica\Document;
use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Test\Base as BaseTest;

class HttpTest extends BaseTest
{
    /**
     * Return transport configuration and the expected HTTP method.
     *
     * @return array[]
     */
    public function getConfig()
    {
        return array(
            array(
                array('transport' => 'Http', 'curl' => array(CURLINFO_HEADER_OUT => true)),
                'GET',
            ),
            array(
                array('transport' => array('type' => 'Http', 'postWithRequestBody' => false, 'curl' => array(CURLINFO_HEADER_OUT => true))),
                'GET',
            ),
            array(
                array('transport' => array('type' => 'Http', 'postWithRequestBody' => true, 'curl' => array(CURLINFO_HEADER_OUT => true))),
                'POST',
            ),
        );
    }

    /**
     * @group functional
     * @dataProvider getConfig
     */
    public function testDynamicHttpMethodBasedOnConfigParameter(array $config, $httpMethod)
    {
        $client = $this->_getClient($config);

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
     * @group functional
     * @dataProvider getConfig
     */
    public function testDynamicHttpMethodOnlyAffectsRequestsWithBody(array $config, $httpMethod)
    {
        $client = $this->_getClient($config);

        $status = $client->getStatus();
        $info = $status->getResponse()->getTransferInfo();
        $this->assertStringStartsWith('GET', $info['request_header']);
    }

    /**
     * @group functional
     */
    public function testCurlNobodyOptionIsResetAfterHeadRequest()
    {
        $client = $this->_getClient();
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

    /**
     * @group functional
     */
    public function testUnicodeData()
    {
        $client = $this->_getClient();
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

    /**
     * @group functional
     */
    public function testWithEnvironmentalProxy()
    {
        putenv('http_proxy='.$this->_getProxyUrl().'/');

        $client = $this->_getClient();
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);

        $client->getConnection()->setProxy(null); // will not change anything
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);

        putenv('http_proxy=');
    }

    /**
     * @group functional
     */
    public function testWithEnabledEnvironmentalProxy()
    {
        putenv('http_proxy='.$this->_getProxyUrl403().'/');
        $client = $this->_getClient();
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(403, $transferInfo['http_code']);
        $client = $this->_getClient();
        $client->getConnection()->setProxy('');
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
        putenv('http_proxy=');
    }

    /**
     * @group functional
     */
    public function testWithProxy()
    {
        $client = $this->_getClient();
        $client->getConnection()->setProxy($this->_getProxyUrl());

        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
    }

    /**
     * @group functional
     */
    public function testWithoutProxy()
    {
        $client = $this->_getClient();
        $client->getConnection()->setProxy('');

        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
    }

    /**
     * @group functional
     */
    public function testBodyReuse()
    {
        $client = $this->_getClient();

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

    /**
     * @group functional
     */
    public function testPostWith0Body()
    {
        $client = $this->_getClient();

        $index = $client->getIndex('elastica_0_body');
        $index->create(array(), true);
        $this->_waitForAllocation($index);
        $index->refresh();

        $tokens = $index->analyze('0');

        $this->assertNotEmpty($tokens);
    }

    protected function tearDown()
    {
        parent::tearDown();
        putenv('http_proxy=');
    }
}
