<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Connection;
use Elastica\Index;
use Elasticsearch\Endpoints\Ingest\Pipeline\Put;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Test as TestUtil;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
class Base extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $hasGroup = $this->_isUnitGroup() || $this->_isFunctionalGroup() || $this->_isBenchmarkGroup();
        $this->assertTrue($hasGroup, 'Every test must have one of "unit", "functional", "benchmark" group');
        $this->showDeprecated();
    }

    protected function tearDown(): void
    {
        if ($this->_isFunctionalGroup()) {
            $this->_getClient()->getIndex('_all')->delete();
            $this->_getClient()->getIndex('_all')->clearCache();
        }

        parent::tearDown();
    }

    protected static function hideDeprecated(): void
    {
        \error_reporting(\error_reporting() & ~E_USER_DEPRECATED);
    }

    protected static function showDeprecated(): void
    {
        \error_reporting(\error_reporting() | E_USER_DEPRECATED);
    }

    /**
     * @param array $params Additional configuration params. Host and Port are already set
     */
    protected function _getClient(array $params = [], ?callable $callback = null, ?LoggerInterface $logger = null): Client
    {
        $config = [
            'host' => $this->_getHost(),
            'port' => $this->_getPort(),
        ];

        $config = \array_merge($config, $params);

        return new Client($config, $callback, $logger);
    }

    /**
     * @return string Host to es for elastica tests
     */
    protected function _getHost()
    {
        return \getenv('ES_HOST') ?: Connection::DEFAULT_HOST;
    }

    /**
     * @return int Port to es for elastica tests
     */
    protected function _getPort()
    {
        return \getenv('ES_PORT') ?: Connection::DEFAULT_PORT;
    }

    protected function _getProxyUrl(): string
    {
        $proxyHost = \getenv('PROXY_HOST') ?: Connection::DEFAULT_HOST;

        return 'http://'.$proxyHost.':8000';
    }

    protected function _getProxyUrl403(): string
    {
        $proxyHost = \getenv('PROXY_HOST') ?: Connection::DEFAULT_HOST;

        return 'http://'.$proxyHost.':8001';
    }

    protected function _createIndex(?string $name = null, bool $delete = true, int $shards = 1): Index
    {
        $name = $name ?: static::buildUniqueId();

        $client = $this->_getClient();
        $index = $client->getIndex($name);

        $index->create(['settings' => ['index' => ['number_of_shards' => $shards, 'number_of_replicas' => 1]]], $delete);

        return $index;
    }

    protected static function buildUniqueId(): string
    {
        return \preg_replace('/[^a-z]/i', '', \strtolower(static::class).\uniqid());
    }

    protected function _createRenamePipeline(): void
    {
        $client = $this->_getClient();

        $endpoint = new Put();
        $endpoint->setID('renaming');
        $endpoint->setBody([
            'description' => 'Rename field',
            'processors' => [
                [
                    'rename' => [
                        'field' => 'old',
                        'target_field' => 'new',
                    ],
                ],
            ],
        ]);

        $client->requestEndpoint($endpoint);
    }

    protected function _checkPlugin($plugin): void
    {
        $nodes = $this->_getClient()->getCluster()->getNodes();
        if (!$nodes[0]->getInfo()->hasPlugin($plugin)) {
            $this->markTestSkipped($plugin.' plugin not installed.');
        }
    }

    protected function _getVersion()
    {
        $data = $this->_getClient()->request('/')->getData();

        return \substr($data['version']['number'], 0, 1);
    }

    protected function _checkVersion($version): void
    {
        $data = $this->_getClient()->request('/')->getData();
        $installedVersion = $data['version']['number'];

        if (\version_compare($installedVersion, $version) < 0) {
            $this->markTestSkipped('Test require '.$version.'+ version of Elasticsearch');
        }
    }

    protected function _checkConnection($host, $port): void
    {
        $fp = @\pfsockopen($host, $port);

        if (!$fp) {
            $this->markTestSkipped('Connection to '.$host.':'.$port.' failed');
        }
    }

    protected function _waitForAllocation(Index $index): void
    {
        do {
            $state = $index->getClient()->getCluster()->getState();
            $indexState = $state['routing_table']['indices'][$index->getName()];

            $allocated = true;
            foreach ($indexState['shards'] as $shard) {
                if ('STARTED' !== $shard[0]['state']) {
                    $allocated = false;
                }
            }
        } while (!$allocated);
    }

    protected function _isUnitGroup()
    {
        $groups = TestUtil::getGroups(\get_class($this), $this->getName(false));

        return \in_array('unit', $groups);
    }

    protected function _isFunctionalGroup()
    {
        $groups = TestUtil::getGroups(\get_class($this), $this->getName(false));

        return \in_array('functional', $groups);
    }

    protected function _isBenchmarkGroup()
    {
        $groups = TestUtil::getGroups(\get_class($this), $this->getName(false));

        return \in_array('benchmark', $groups);
    }
}
