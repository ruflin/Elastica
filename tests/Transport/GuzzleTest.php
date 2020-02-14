<?php

namespace Elastica\Test\Transport;

use Elastica\Document;
use Elastica\Query;
use Elastica\ResultSet\DefaultBuilder;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class GuzzleTest extends BaseTest
{
    public static function setUpbeforeClass(): void
    {
        if (!\class_exists('GuzzleHttp\\Client')) {
            self::markTestSkipped('guzzlehttp/guzzle package should be installed to run guzzle transport tests');
        }
    }

    protected function setUp(): void
    {
        \putenv('http_proxy=');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        \putenv('http_proxy=');
    }

    /**
     * @group functional
     */
    public function testWithEnvironmentalProxy(): void
    {
        $this->checkProxy($this->_getProxyUrl());
        \putenv('http_proxy='.$this->_getProxyUrl().'/');

        $client = $this->_getClient(['transport' => 'Guzzle', 'persistent' => false]);
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);

        $client->getConnection()->setProxy(null); // will not change anything
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
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

        $client = $this->_getClient(['transport' => 'Guzzle', 'persistent' => false]);
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(403, $transferInfo['http_code']);

        $client = $this->_getClient(['transport' => 'Guzzle', 'persistent' => false]);
        $client->getConnection()->setProxy('');
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);

        \putenv('http_proxy=');
    }

    /**
     * @group functional
     */
    public function testWithProxy(): void
    {
        $this->checkProxy($this->_getProxyUrl());
        $client = $this->_getClient(['transport' => 'Guzzle', 'persistent' => false]);
        $client->getConnection()->setProxy($this->_getProxyUrl());

        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
    }

    /**
     * @group functional
     */
    public function testWithoutProxy(): void
    {
        $client = $this->_getClient(['transport' => 'Guzzle', 'persistent' => false]);
        $client->getConnection()->setProxy('');

        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
    }

    /**
     * @group functional
     */
    public function testBodyReuse(): void
    {
        $client = $this->_getClient(['transport' => 'Guzzle', 'persistent' => false]);
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
     * @group unit
     */
    public function testInvalidConnection(): void
    {
        $this->expectException(\Elastica\Exception\Connection\GuzzleException::class);

        $client = $this->_getClient(['transport' => 'Guzzle', 'port' => 4500, 'persistent' => false]);
        $response = $client->request('_stats', 'GET');
        $client->request('_status', 'GET');
    }

    protected function checkProxy($url): void
    {
        $url = \parse_url($url);
        $this->_checkConnection($url['host'], $url['port']);
    }
}
