<?php
namespace Elastica\Test\Transport;

use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Test\Base as BaseTest;

class TransportBenchmarkTest extends BaseTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->markTestIncomplete('Benchmarks currently skipped with es2.0. Has to be reworked');
    }

    protected $_max = 1000;

    protected $_maxData = 20;

    protected static $_results = [];

    public static function tearDownAfterClass()
    {
        self::printResults();
    }

    /**
     * @param array $config
     *
     * @return \Elastica\Type
     */
    protected function getType(array $config)
    {
        $client = $this->_getClient($config);
        $index = $client->getIndex('benchmark'.uniqid());
        $index->create(['index' => ['number_of_shards' => 1, 'number_of_replicas' => 0]], true);

        return $index->getType('benchmark');
    }

    /**
     * @dataProvider providerTransport
     * @group benchmark
     */
    public function testAddDocument(array $config, $transport)
    {
        $this->_checkTransport($config, $transport);

        $type = $this->getType($config);
        $index = $type->getIndex();
        $index->create([], true);

        $times = [];
        for ($i = 0; $i < $this->_max; ++$i) {
            $data = $this->getData($i);
            $doc = new Document($i, $data);
            $result = $type->addDocument($doc);
            $times[] = $result->getQueryTime();
            $this->assertTrue($result->isOk());
        }

        $index->refresh();

        self::logResults('insert', $transport, $times);
    }

    /**
     * @depends testAddDocument
     * @dataProvider providerTransport
     * @group benchmark
     */
    public function testRandomRead(array $config, $transport)
    {
        $this->_checkTransport($config, $transport);

        $type = $this->getType($config);

        $type->search('test');

        $times = [];
        for ($i = 0; $i < $this->_max; ++$i) {
            $test = rand(1, $this->_max);
            $query = new Query();
            $query->setQuery(new \Elastica\Query\MatchAll());
            $query->setPostFilter(new \Elastica\Filter\Term(['test' => $test]));
            $result = $type->search($query);
            $times[] = $result->getResponse()->getQueryTime();
        }

        self::logResults('random read', $transport, $times);
    }

    /**
     * @depends testAddDocument
     * @dataProvider providerTransport
     * @group benchmark
     */
    public function testBulk(array $config, $transport)
    {
        $this->_checkTransport($config, $transport);

        $type = $this->getType($config);

        $times = [];
        for ($i = 0; $i < $this->_max; ++$i) {
            $docs = [];
            for ($j = 0; $j < 10; ++$j) {
                $data = $this->getData($i.$j);
                $docs[] = new Document($i, $data);
            }

            $result = $type->addDocuments($docs);
            $times[] = $result->getQueryTime();
        }

        self::logResults('bulk', $transport, $times);
    }

    /**
     * @dataProvider providerTransport
     * @group benchmark
     */
    public function testGetMapping(array $config, $transport)
    {
        $this->_checkTransport($config, $transport);

        $client = $this->_getClient($config);
        $index = $client->getIndex('benchmark');
        $index->create([], true);
        $type = $index->getType('mappingTest');

        // Define mapping
        $mapping = new \Elastica\Type\Mapping();
        $mapping->setParam('_boost', ['name' => '_boost', 'null_value' => 1.0]);
        $mapping->setProperties([
            'id' => ['type' => 'integer', 'include_in_all' => false],
            'user' => [
                'type' => 'object',
                'properties' => [
                    'name' => ['type' => 'string', 'include_in_all' => true],
                    'fullName' => ['type' => 'string', 'include_in_all' => true],
                ],
            ],
            'msg' => ['type' => 'string', 'include_in_all' => true],
            'tstamp' => ['type' => 'date', 'include_in_all' => false],
            'location' => ['type' => 'geo_point', 'include_in_all' => false],
            '_boost' => ['type' => 'float', 'include_in_all' => false],
        ]);

        $type->setMapping($mapping);
        $index->refresh();

        $times = [];
        for ($i = 0; $i < $this->_max; ++$i) {
            $response = $type->request('_mapping', \Elastica\Request::GET);
            $times[] = $response->getQueryTime();
        }
        self::logResults('get mapping', $transport, $times);
    }

    public function providerTransport()
    {
        return [
            [
                [
                    'transport' => 'Http',
                    'host' => $this->_getHost(),
                    'port' => $this->_getPort(),
                    'persistent' => false,
                ],
                'Http:NotPersistent',
            ],
            [
                [
                    'transport' => 'Http',
                    'host' => $this->_getHost(),
                    'port' => $this->_getPort(),
                    'persistent' => true,
                ],
                'Http:Persistent',
            ],
        ];
    }

    /**
     * @param string $test
     *
     * @return array
     */
    protected function getData($test)
    {
        $data = [
            'test' => $test,
            'name' => [],
        ];
        for ($i = 0; $i < $this->_maxData; ++$i) {
            $data['name'][] = uniqid();
        }

        return $data;
    }

    /**
     * @param $name
     * @param $transport
     * @param array $times
     */
    protected static function logResults($name, $transport, array $times)
    {
        self::$_results[$name][$transport] = [
            'count' => count($times),
            'max' => max($times) * 1000,
            'min' => min($times) * 1000,
            'mean' => (array_sum($times) / count($times)) * 1000,
        ];
    }

    protected static function printResults()
    {
        echo sprintf(
            "\n%-12s | %-20s | %-12s | %-12s | %-12s | %-12s\n\n",
            'NAME',
            'TRANSPORT',
            'COUNT',
            'MAX',
            'MIN',
            'MEAN',
            '%'
        );
        foreach (self::$_results as $name => $values) {
            $means = [];
            foreach ($values as $times) {
                $means[] = $times['mean'];
            }
            $minMean = min($means);
            foreach ($values as $transport => $times) {
                $perc = 0;

                if ($minMean != 0) {
                    $perc = (($times['mean'] - $minMean) / $minMean) * 100;
                }

                echo sprintf(
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

    protected function _checkTransport(array $config, $transport)
    {
        $this->_checkConnection($config['host'], $config['port']);
    }
}
