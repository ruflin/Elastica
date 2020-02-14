<?php

namespace Elastica\Test\Transport;

use Elastica\Document;
use Elastica\Query;
use Elastica\ResultSet\DefaultBuilder;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class HttpTest extends BaseTest
{
    protected function tearDown(): void
    {
        parent::tearDown();
        \putenv('http_proxy=');
    }

    /**
     * @group functional
     */
    public function testCurlNobodyOptionIsResetAfterHeadRequest(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('curl_test');
        $index->create([], true);
        $this->_waitForAllocation($index);

        // Force HEAD request to set CURLOPT_NOBODY = true
        $index->exists();

        $id = 1;
        $data = ['id' => $id, 'name' => 'Item 1'];
        $doc = new Document($id, $data);

        $index->addDocument($doc);

        $index->refresh();

        $doc = $index->getDocument($id);

        // Document should be retrieved correctly
        $this->assertSame($data, $doc->getData());
        $this->assertEquals($id, $doc->getId());
    }

    /**
     * @group functional
     */
    public function testUnicodeData(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('curl_test');
        $index->create([], true);
        $this->_waitForAllocation($index);

        // Force HEAD request to set CURLOPT_NOBODY = true
        $index->exists();

        $id = 22;
        $data = ['id' => $id, 'name' => '
            Сегодня, я вижу, особенно грустен твой взгляд, /
            И руки особенно тонки, колени обняв. /
            Послушай: далеко, далеко, на озере Чад /
            Изысканный бродит жираф.'];

        $doc = new Document($id, $data);

        $index->addDocument($doc);

        $index->refresh();

        $doc = $index->getDocument($id);

        // Document should be retrieved correctly
        $this->assertSame($data, $doc->getData());
        $this->assertEquals($id, $doc->getId());
    }

    /**
     * @group functional
     */
    public function testWithEnvironmentalProxy(): void
    {
        $this->checkProxy($this->_getProxyUrl());
        \putenv('http_proxy='.$this->_getProxyUrl().'/');

        $client = $this->_getClient();
        $transferInfo = $client->request('_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);

        $client->getConnection()->setProxy(null); // will not change anything
        $transferInfo = $client->request('_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);

        \putenv('http_proxy=');
    }

    /**
     * @group functional
     */
    public function testWithEnabledEnvironmentalProxy(): void
    {
        $this->checkProxy($this->_getProxyUrl403());
        \putenv('http_proxy='.$this->_getProxyUrl403().'/');
        $client = $this->_getClient();
        $transferInfo = $client->request('_nodes')->getTransferInfo();
        $this->assertEquals(403, $transferInfo['http_code']);
        $client = $this->_getClient();
        $client->getConnection()->setProxy('');
        $transferInfo = $client->request('_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
        \putenv('http_proxy=');
    }

    /**
     * @group functional
     */
    public function testWithProxy(): void
    {
        $this->checkProxy($this->_getProxyUrl());
        $client = $this->_getClient();
        $client->getConnection()->setProxy($this->_getProxyUrl());

        $transferInfo = $client->request('_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
    }

    /**
     * @group functional
     */
    public function testWithoutProxy(): void
    {
        $client = $this->_getClient();
        $client->getConnection()->setProxy('');

        $transferInfo = $client->request('_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
    }

    /**
     * @group functional
     */
    public function testBodyReuse(): void
    {
        $client = $this->_getClient();

        $index = $client->getIndex('elastica_body_reuse_test');
        $index->create([], true);
        $this->_waitForAllocation($index);

        $index->addDocument(new Document(1, ['test' => 'test']));

        $index->refresh();

        $resultSet = $index->search([
            'query' => [
                'query_string' => [
                    'query' => 'pew pew pew',
                ],
            ],
        ]);

        $this->assertEquals(0, $resultSet->getTotalHits());

        $response = $index->request('/_search', 'POST');

        $builder = new DefaultBuilder();
        $resultSet = $builder->buildResultSet($response, Query::create([]));

        $this->assertEquals(1, $resultSet->getTotalHits());
    }

    /**
     * @group functional
     */
    public function testRequestSuccessWithHttpCompressionEnabled(): void
    {
        $client = $this->_getClient(['transport' => ['type' => 'Http', 'compression' => true, 'curl' => [CURLINFO_HEADER_OUT => true]]]);

        $index = $client->getIndex('elastica_request_with_body_and_http_compression_enabled');

        $createIndexResponse = $index->create([], true);

        $createIndexResponseTransferInfo = $createIndexResponse->getTransferInfo();
        $this->assertRegExp('/Accept-Encoding:\ (gzip|deflate)/', $createIndexResponseTransferInfo['request_header']);
        $this->assertArrayHasKey('acknowledged', $createIndexResponse->getData());
    }

    /**
     * @group functional
     */
    public function testRequestSuccessWithHttpCompressionDisabled(): void
    {
        $client = $this->_getClient(['transport' => ['type' => 'Http', 'compression' => false, 'curl' => [CURLINFO_HEADER_OUT => true]]]);

        $index = $client->getIndex('elastica_request_with_body_and_http_compression_disabled');

        $createIndexResponse = $index->create([], true);

        $createIndexResponseTransferInfo = $createIndexResponse->getTransferInfo();
        $this->assertRegExp('/Accept-Encoding:\ (gzip|deflate)/', $createIndexResponseTransferInfo['request_header']);
        $this->assertArrayHasKey('acknowledged', $createIndexResponse->getData());
    }

    protected function checkProxy($url): void
    {
        $url = \parse_url($url);
        $this->_checkConnection($url['host'], $url['port']);
    }
}
