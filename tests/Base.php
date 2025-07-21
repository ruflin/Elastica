<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastic\Transport\NodePool\Resurrect\NoResurrect;
use Elastic\Transport\NodePool\Selector\RoundRobin;
use Elastica\Client;
use Elastica\Index;
use Elastica\Test\Transport\NodePool\TraceableSimpleNodePool;
use PHPUnit\Framework\TestCase;
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
        \error_reporting(\error_reporting() & ~\E_USER_DEPRECATED);
    }

    protected static function showDeprecated(): void
    {
        \error_reporting(\error_reporting() | \E_USER_DEPRECATED);
    }

    /**
     * @param array $config Additional configuration params. Host and Port are already set
     */
    protected function _getClient(array $config = [], ?LoggerInterface $logger = null): Client
    {
        $config['hosts'] ??= [$this->_getHost().':'.$this->_getPort()];

        $config['transport_config']['node_pool'] ??= new TraceableSimpleNodePool(
            new RoundRobin(),
            new NoResurrect()
        );

        return new Client($config, $logger);
    }

    protected function _getHost(): string
    {
        return \getenv('ES_HOST') ?: 'localhost';
    }

    protected function _getPort(): int
    {
        return \getenv('ES_PORT') ?: 9200;
    }

    protected function _getProxyUrl(): string
    {
        $proxyHost = \getenv('PROXY_HOST') ?: 'localhost';

        return 'http://'.$proxyHost.':8000';
    }

    protected function _getProxyUrl403(): string
    {
        $proxyHost = \getenv('PROXY_HOST') ?: 'localhost';

        return 'http://'.$proxyHost.':8001';
    }

    protected function _createIndex(?string $name = null, bool $delete = true, int $shards = 1): Index
    {
        $name = $name ?: static::buildUniqueId();

        $client = $this->_getClient();
        $index = $client->getIndex($name);

        $index->create(['settings' => ['index' => ['number_of_shards' => $shards, 'number_of_replicas' => 1]]], [
            'recreate' => $delete,
        ]);

        return $index;
    }

    protected static function buildUniqueId(): string
    {
        return \preg_replace('/[^a-z]/i', '', \strtolower(static::class).\uniqid());
    }

    protected function _createRenamePipeline(): void
    {
        $client = $this->_getClient();

        $body = [
            'description' => 'Rename field',
            'processors' => [
                [
                    'rename' => [
                        'field' => 'old',
                        'target_field' => 'new',
                    ],
                ],
            ],
        ];

        $client->ingest()->putPipeline(['id' => 'renaming', 'body' => $body]);
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
        $data = $this->_getClient()->info()->asArray();

        return \substr($data['version']['number'], 0, 1);
    }

    protected function _checkVersion($version): void
    {
        $data = $this->_getClient()->info()->asArray();
        $installedVersion = $data['version']['number'];

        if (\version_compare($installedVersion, $version) < 0) {
            $this->markTestSkipped('Test require '.$version.'+ version of Elasticsearch');
        }
    }

    protected function _waitForAllocation(Index $index): void
    {
        do {
            $state = $index->getClient()->getCluster()->getState();
            $indexState = $state['routing_table']['indices'][$index->getName()];

            $allocated = true;
            foreach ($indexState['shards'] as $shards) {
                foreach ($shards as $shard) {
                    if ('STARTED' !== $shard['state']) {
                        $allocated = false;
                    }
                }
            }
        } while (!$allocated);
    }

    protected function _isUnitGroup(): bool
    {
        return \in_array('unit', $this->groups(), true);
    }

    protected function _isFunctionalGroup(): bool
    {
        return \in_array('functional', $this->groups(), true);
    }

    protected function _isBenchmarkGroup(): bool
    {
        return \in_array('benchmark', $this->groups(), true);
    }
}
