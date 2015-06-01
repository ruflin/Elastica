<?php
namespace Elastica\Test\Transport;

use Elastica\Document;
use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Test\Base as BaseTest;

class GuzzleTest extends BaseTest
{
    public static function setUpBeforeClass()
    {
        if (version_compare(phpversion(), 7, '>=')) {
            self::markTestSkipped('Http tests currently do not work with PHP 7');
        }

        if (!class_exists('GuzzleHttp\\Client')) {
            self::markTestSkipped('guzzlehttp/guzzle package should be installed to run guzzle transport tests');
        }
    }

    /**
     * Return transport configuration and the expected HTTP method.
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
     * @group functional
     * @dataProvider getConfig
     */
    public function testDynamicHttpMethodBasedOnConfigParameter(array $config, $httpMethod)
    {
        $client = $this->_getClient($config);

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
    public function testWithEnvironmentalProxy()
    {
        putenv('http_proxy='.$this->_getProxyUrl().'/');

        $client = $this->_getClient(array('transport' => 'Guzzle', 'persistent' => false));
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

        $client = $this->_getClient(array('transport' => 'Guzzle', 'persistent' => false));
        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(403, $transferInfo['http_code']);

        $client = $this->_getClient(array('transport' => 'Guzzle', 'persistent' => false));
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
        $client = $this->_getClient(array('transport' => 'Guzzle', 'persistent' => false));
        $client->getConnection()->setProxy($this->_getProxyUrl());

        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
    }

    /**
     * @group functional
     */
    public function testWithoutProxy()
    {
        $client = $this->_getClient(array('transport' => 'Guzzle', 'persistent' => false));
        $client->getConnection()->setProxy('');

        $transferInfo = $client->request('/_nodes')->getTransferInfo();
        $this->assertEquals(200, $transferInfo['http_code']);
    }

    /**
     * @group functional
     */
    public function testBodyReuse()
    {
        $client = $this->_getClient(array('transport' => 'Guzzle', 'persistent' => false));

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
     * @group unit
     * @expectedException Elastica\Exception\Connection\GuzzleException
     */
    public function testInvalidConnection()
    {
        $client = $this->_getClient(array('transport' => 'Guzzle', 'port' => 4500, 'persistent' => false));
        $response = $client->request('_status', 'GET');
    }

    protected function tearDown()
    {
        parent::tearDown();
        putenv('http_proxy=');
    }
}
