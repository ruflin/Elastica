<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Connection;
use Elastica\Index;

class Base extends \PHPUnit_Framework_TestCase
{
    public static function hideDeprecated()
    {
        error_reporting(error_reporting() & ~E_USER_DEPRECATED);
    }

    public static function showDeprecated()
    {
        error_reporting(error_reporting() | E_USER_DEPRECATED);
    }

    protected function assertFileDeprecated($file, $deprecationMessage)
    {
        $content = file_get_contents($file);
        $content = preg_replace('/^(abstract class|class) ([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]+)/m', '${1} ${2}'.uniqid(), $content);
        $newFile = tempnam(sys_get_temp_dir(), 'elastica-test-');
        file_put_contents($newFile, $content);

        $errorsCollector = $this->startCollectErrors();

        require $newFile;
        unlink($newFile);

        $this->finishCollectErrors();
        $errorsCollector->assertOnlyOneDeprecatedError($deprecationMessage);
    }

    /**
     * @return ErrorsCollector
     */
    protected function startCollectErrors()
    {
        $errorsCollector = new ErrorsCollector($this);

        set_error_handler(function () use ($errorsCollector) {
            $errorsCollector->add(func_get_args());
        });

        return $errorsCollector;
    }

    protected function finishCollectErrors()
    {
        restore_error_handler();
    }

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

    protected function _checkScriptInlineSetting()
    {
        $nodes = $this->_getClient()->getCluster()->getNodes();
        $scriptInline = $nodes[0]->getInfo()->get('settings', 'script', 'inline');

        if ($scriptInline != 'on') {
            $this->markTestSkipped('script.inline is not enabled. This is required for this test');
        }
    }

    protected function _checkPlugin($plugin)
    {
        $nodes = $this->_getClient()->getCluster()->getNodes();
        if (!$nodes[0]->getInfo()->hasPlugin($plugin)) {
            $this->markTestSkipped($plugin.' plugin not installed.');
        }
    }

    protected function _checkVersion($version)
    {
        $data = $this->_getClient()->request('/')->getData();
        $installedVersion = $data['version']['number'];

        if (version_compare($installedVersion, $version) < 0) {
            $this->markTestSkipped('Test require '.$version.'+ version of Elasticsearch');
        }
    }

    protected function _checkConnection($host, $port)
    {
        $fp = @pfsockopen($host, $port);

        if (!$fp) {
            $this->markTestSkipped('Connection to '.$host.':'.$port.' failed');
        }
    }

    protected function _waitForAllocation(Index $index)
    {
        do {
            $state = $index->getClient()->getCluster()->getState();
            $indexState = $state['routing_table']['indices'][$index->getName()];

            $allocated = true;
            foreach ($indexState['shards'] as $shard) {
                if ($shard[0]['state'] != 'STARTED') {
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
        $this->showDeprecated();
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
}
