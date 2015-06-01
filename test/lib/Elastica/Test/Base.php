<?php
namespace Elastica\Test;

use Elastica\Client;
use Elastica\Connection;
use Elastica\Index;

class Base extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array    $params   Additional configuration params. Host and Port are already set
     * @param callback $callback
     *
     * @return Client
     */
    protected function _getClient(array $params = array(), $callback = null)
    {
        $config = array(
            'host' => $this->_getHost(),
            'port' => $this->_getPort(),
        );

        $config = array_merge($config, $params);

        return new Client($config, $callback);
    }

    /**
     * @return string Host to es for elastica tests
     */
    protected function _getHost()
    {
        return getenv('ES_HOST') ?: Connection::DEFAULT_HOST;
    }

    /**
     * @return int Port to es for elastica tests
     */
    protected function _getPort()
    {
        return getenv('ES_PORT') ?: Connection::DEFAULT_PORT;
    }

    /**
     * @return string Proxy url string
     */
    protected function _getProxyUrl()
    {
        $proxyHost = getenv('PROXY_HOST') ?: Connection::DEFAULT_HOST;

        return 'http://'.$proxyHost.':12345';
    }

    /**
     * @return string Proxy url string to proxy which returns 403
     */
    protected function _getProxyUrl403()
    {
        $proxyHost = getenv('PROXY_HOST') ?: Connection::DEFAULT_HOST;

        return 'http://'.$proxyHost.':12346';
    }

    /**
     * @param string $name   Index name
     * @param bool   $delete Delete index if it exists
     * @param int    $shards Number of shards to create
     *
     * @return \Elastica\Index
     */
    protected function _createIndex($name = null, $delete = true, $shards = 1)
    {
        if (is_null($name)) {
            $name = preg_replace('/[^a-z]/i', '', strtolower(get_called_class())).uniqid();
        }

        $client = $this->_getClient();
        $index = $client->getIndex('elastica_'.$name);
        $index->create(array('index' => array('number_of_shards' => $shards, 'number_of_replicas' => 0)), $delete);

        return $index;
    }

    protected function _waitForAllocation(Index $index)
    {
        do {
            $settings = $index->getStatus()->get();
            $allocated = true;
            foreach ($settings['shards'] as $shard) {
                if ($shard[0]['routing']['state'] != 'STARTED') {
                    $allocated = false;
                }
            }
        } while (!$allocated);
    }

    protected function setUp()
    {
        parent::setUp();

        $hasGroup = $this->_isUnitGroup() || $this->_isFunctionalGroup() || $this->_isShutdownGroup() || $this->_isBenchmarkGroup();
        $this->assertTrue($hasGroup, 'Every test must have one of "unit", "functional", "shutdown" or "benchmark" group');
    }

    protected function tearDown()
    {
        if ($this->_isFunctionalGroup()) {
            $this->_getClient()->getIndex('_all')->delete();
            $this->_getClient()->getIndex('_all')->clearCache();
        }

        parent::tearDown();
    }

    protected function _isUnitGroup()
    {
        $groups = \PHPUnit_Util_Test::getGroups(get_class($this), $this->getName(false));

        return in_array('unit', $groups);
    }

    protected function _isFunctionalGroup()
    {
        $groups = \PHPUnit_Util_Test::getGroups(get_class($this), $this->getName(false));

        return in_array('functional', $groups);
    }

    protected function _isShutdownGroup()
    {
        $groups = \PHPUnit_Util_Test::getGroups(get_class($this), $this->getName(false));

        return in_array('shutdown', $groups);
    }

    protected function _isBenchmarkGroup()
    {
        $groups = \PHPUnit_Util_Test::getGroups(get_class($this), $this->getName(false));

        return in_array('benchmark', $groups);
    }

    /**
     * Skips test if debugging is not enabled or not set.
     */
    protected static function _checkDebug()
    {
        if (!\Elastica\Util::debugEnabled()) {
            self::markTestSkipped('The DEBUG constant must be set to true for this test to run');
        }
    }
}
