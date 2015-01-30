<?php

namespace Elastica\Test\Transport;

use Elastica\Client;
use Elastica\Document;
use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Test\Base as BaseTest;

class GuzzleTest extends BaseTest
{
    public static function setUpBeforeClass()
    {
        if (!class_exists('GuzzleHttp\\Client')) {
            self::markTestSkipped('guzzlehttp/guzzle package should be installed to run guzzle transport tests');
        }
    }

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
                array('persistent' => false, 'transport' => 'Guzzle'),
                'GET',
            ),
            array(
                array('persistent' => false, 'transport' => array('type' => 'Guzzle', 'postWithRequestBody' => false)),
                'GET',
            ),
            array(
                array('persistent' => false, 'transport' => array('type' => 'Guzzle', 'postWithRequestBody' => true)),
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

    public function testWithEnvironmentalProxy()
    {
        putenv('http_proxy=http://127.0.0.1:12345/');

        $client = new \Elastica\Client(array('transport' => 'Guzzle', 'persistent' => false));
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

        $client = new \Elastica\Client(array('transport' => 'Guzzle', 'persistent' => false));
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(403, $transferInfo['http_code']);

        $client = new \Elastica\Client(array('transport' => 'Guzzle', 'persistent' => false));
        $client->getConnection()->setProxy('');
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);

        putenv('http_proxy=');
    }

    public function testWithProxy()
    {
        $client = new \Elastica\Client(array('transport' => 'Guzzle', 'persistent' => false));
        $client->getConnection()->setProxy('http://127.0.0.1:12345');

        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
    }

    public function testWithoutProxy()
    {
        $client = new \Elastica\Client(array('transport' => 'Guzzle', 'persistent' => false));
        $client->getConnection()->setProxy('');

        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
    }

    public function testBodyReuse()
    {
        $client = new Client(array('transport' => 'Guzzle', 'persistent' => false));

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
     * @expectedException Elastica\Exception\Connection\GuzzleException
     */
    public function testInvalidConnection()
    {
        $client = new Client(array('transport' => 'Guzzle', 'port' => 4500, 'persistent' => false));
        $response = $client->request('_status', 'GET');
    }
}
