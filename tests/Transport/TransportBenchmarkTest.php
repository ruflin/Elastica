<?php

namespace Elastica\Test\Transport;

use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Query\Term;
use Elastica\Request;
use Elastica\Test\Base as BaseTest;

/**
 * @group benchmark
 *
 * @internal
 */
class TransportBenchmarkTest extends BaseTest
{
    protected $_max = 1000;
    protected $_maxData = 20;
    protected static $_results = [];

    public static function tearDownAfterClass(): void
    {
        self::printResults();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('Benchmarks currently skipped: it has to be reworked');
    }

    /**
     * @dataProvider providerTransport
     */
    public function testAddDocument(array $config, string $transport): void
    {
        $this->_checkTransport($config, $transport);
        $index = $this->getIndex($config);
        $index->create([], true);

        $times = [];
        for ($i = 0; $i < $this->_max; ++$i) {
            $data = $this->getData($i);
            $doc = new Document($i, $data);
            $result = $index->addDocument($doc);
            $times[] = $result->getQueryTime();
            $this->assertTrue($result->isOk());
        }

        $index->refresh();

        self::logResults('insert', $transport, $times);
    }

    /**
     * @depends testAddDocument
     * @dataProvider providerTransport
     *
     * @param mixed $transport
     */
    public function testRandomRead(array $config, $transport): void
    {
        $this->_checkTransport($config, $transport);
        $index = $this->getIndex($config);
        $index->search('test');

        $times = [];
        for ($i = 0; $i < $this->_max; ++$i) {
            $test = \rand(1, $this->_max);
            $query = new Query();
            $query->setQuery(new MatchAll());
            $query->setPostFilter(new Term(['test' => $test]));
            $result = $index->search($query);
            $times[] = $result->getResponse()->getQueryTime();
        }

        self::logResults('random read', $transport, $times);
    }

    /**
     * @depends testAddDocument
     * @dataProvider providerTransport
     *
     * @param mixed $transport
     */
    public function testBulk(array $config, $transport): void
    {
        $this->_checkTransport($config, $transport);
        $index = $this->getIndex($config);

        $times = [];
        for ($i = 0; $i < $this->_max; ++$i) {
            $docs = [];
            for ($j = 0; $j < 10; ++$j) {
                $data = $this->getData($i.$j);
                $docs[] = new Document($i, $data);
            }

            $result = $index->addDocuments($docs);
            $times[] = $result->getQueryTime();
        }

        self::logResults('bulk', $transport, $times);
    }

    /**
     * @dataProvider providerTransport
     */
    public function testGetMapping(array $config, string $transport): void
    {
        $this->_checkTransport($config, $transport);

        $client = $this->_getClient($config);
        $index = $client->getIndex('benchmark');
        $index->create([], true);

        // Define mapping
        $mapping = new Mapping();
        $mapping->setParam('_boost', ['name' => '_boost', 'null_value' => 1.0]);
        $mapping->setProperties([
            'id' => ['type' => 'integer'],
            'user' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'text', 'copy_to' => 'allincluded'],
                    'fullName' => ['type' => 'text', 'copy_to' => 'allincluded'],
                ],
            ],
            'msg' => ['type' => 'text', 'copy_to' => 'allincluded'],
            'tstamp' => ['type' => 'date'],
            'location' => ['type' => 'geo_point'],
            '_boost' => ['type' => 'float'],
            'allincluded' => ['type' => 'text'],
        ]);

        $index->setMapping($mapping);
        $index->refresh();

        $times = [];
        for ($i = 0; $i < $this->_max; ++$i) {
            $response = $index->request('_mapping', Request::GET);
            $times[] = $response->getQueryTime();
        }
        self::logResults('get mapping', $transport, $times);
    }

    public function providerTransport(): iterable
    {
        yield [[
            'transport' => 'Http',
            'host' => $this->_getHost(),
            'port' => $this->_getPort(),
            'persistent' => false,
        ],
            'Http:NotPersistent',
        ];
        yield [[
            'transport' => 'Http',
            'host' => $this->_getHost(),
            'port' => $this->_getPort(),
            'persistent' => true,
        ],
            'Http:Persistent',
        ];
    }

    protected function getIndex(array $config): Index
    {
        $client = $this->_getClient($config);
        $index = $client->getIndex('benchmark'.self::buildUniqueId());
        $index->create(['index' => ['number_of_shards' => 1, 'number_of_replicas' => 0]], true);

        return $index;
    }

    protected function getData(string $test): array
    {
        $data = [
            'test' => $test,
            'name' => [],
        ];
        for ($i = 0; $i < $this->_maxData; ++$i) {
            $data['name'][] = self::buildUniqueId();
        }

        return $data;
    }

    protected static function logResults(string $name, string $transport, array $times): void
    {
        self::$_results[$name][$transport] = [
            'count' => \count($times),
            'max' => \max($times) * 1000,
            'min' => \min($times) * 1000,
            'mean' => (\array_sum($times) / \count($times)) * 1000,
        ];
    }

    protected static function printResults(): void
    {
        if (!\count(self::$_results)) {
            return;
        }

        echo \sprintf(
            "\n%-12s | %-20s | %-12s | %-12s | %-12s | %-12s\n\n",
            'NAME',
            'TRANSPORT',
            'COUNT',
            'MAX',
            'MIN',
            'MEAN'
        );
        foreach (self::$_results as $name => $values) {
            $means = [];
            foreach ($values as $times) {
                $means[] = $times['mean'];
            }
            $minMean = \min($means);
            foreach ($values as $transport => $times) {
                $perc = 0;

                if (0 !== $minMean) {
                    $perc = (($times['mean'] - $minMean) / $minMean) * 100;
                }

                echo \sprintf(
                    "%-12s | %-20s | %-12d | %-12.2f | %-12.2f | %-12.2f | %+03.2f\n",
                    $name,
                    $transport,
                    $times['count'],
                    $times['max'],
                    $times['min'],
                    $times['mean'],
                    $perc
                );
            }
            echo "\n";
        }
    }

    protected function _checkTransport(array $config, $transport): void
    {
        $this->_checkConnection($config['host'], $config['port']);
    }
}
