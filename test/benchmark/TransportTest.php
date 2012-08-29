<?php

require_once dirname(__FILE__) . '/../bootstrap.php';

/**
 * @backupGlobals disabled
 */
class TransportTest extends PHPUnit_Framework_TestCase
{
    protected $_max = 1000;
    protected $_maxData = 20;
    static protected $_results = array();

    public static function setUpBeforeClass()
    {
        if (!defined('DEBUG')) {
            define('DEBUG', true);
        } else if (false == DEBUG) {
            self::markTestIncomplete('DEBUG const is set to false, it prevents query time measuring.');
        }
    }

    public static function tearDownAfterClass()
    {
        self::printResults();
    }

    /**
     * @param array $config
     * @return Elastica_Type
     */
    protected function getType(array $config)
    {
        $client = new Elastica_Client($config);
        $index = $client->getIndex('test');
        return $index->getType('test');
    }

    /**
     * @dataProvider providerTransport
     */
    public function testAddDocument(array $config, $transport)
    {
        $type = $this->getType($config);
        $index = $type->getIndex();
        $index->create(array(), true);

        $times = array();
        for ($i = 0; $i < $this->_max; $i++) {
            $data = $this->getData($i);
            $doc = new Elastica_Document($i, $data);
            $result = $type->addDocument($doc);
            $times[] = $result->getQueryTime();
            $this->assertTrue($result->isOk());
        }

        $index->refresh();

        $this->logResults('insert', $transport, $times);
    }

    /**
     * @depends testAddDocument
     * @dataProvider providerTransport
     */
    public function testRandomRead(array $config, $transport)
    {
        $type = $this->getType($config);

        $type->search('test');

        $times = array();
        for ($i = 0; $i < $this->_max; $i++) {
            $test = rand(1, $this->_max);
            $query = new Elastica_Query();
            $query->setQuery(new Elastica_Query_MatchAll());
            $query->setFilter(new Elastica_Filter_Term(array('test' => $test)));
            $result = $type->search($query);
            $times[] = $result->getResponse()->getQueryTime();
        }

        $this->logResults('random read', $transport, $times);
    }

    /**
     * @depends testAddDocument
     * @dataProvider providerTransport
     */
    public function testBulk(array $config, $transport)
    {
        $type = $this->getType($config);

        $times = array();
        for ($i = 0; $i < $this->_max; $i++) {
            $docs = array();
            for ($j = 0; $j < 10; $j++) {
                $data = $this->getData($i . $j);
                $docs[] = new Elastica_Document($i, $data);
            }

            $result = $type->addDocuments($docs);
            $times[] = $result->getQueryTime();
        }

        $this->logResults('bulk', $transport, $times);
    }

    /**
     * @dataProvider providerTransport
     */
    public function testGetMapping(array $config, $transport)
    {
        $client = new Elastica_Client($config);
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('mappingTest');

        // Define mapping
        $mapping = new Elastica_Type_Mapping();
        $mapping->setParam('_boost', array('name' => '_boost', 'null_value' => 1.0));
        $mapping->setProperties(array(
            'id'      => array('type' => 'integer', 'include_in_all' => FALSE),
            'user'    => array(
                'type' => 'object',
                'properties' => array(
                    'name'      => array('type' => 'string', 'include_in_all' => TRUE),
                    'fullName'  => array('type' => 'string', 'include_in_all' => TRUE)
                ),
            ),
            'msg'     => array('type' => 'string', 'include_in_all' => TRUE),
            'tstamp'  => array('type' => 'date', 'include_in_all' => FALSE),
            'location'=> array('type' => 'geo_point', 'include_in_all' => FALSE),
            '_boost'  => array('type' => 'float', 'include_in_all' => FALSE)
        ));

        $type->setMapping($mapping);
        $index->refresh();

        $times = array();
        for ($i = 0; $i < $this->_max; $i++) {
            $response = $type->request('_mapping', Elastica_Request::GET);
            $times[] = $response->getQueryTime();
        }
        $this->logResults('get mapping', $transport, $times);
    }

    public function providerTransport()
    {
        return array(
            array(
                array(
                    'transport' => 'Http',
                    'host' => 'localhost',
                    'port' => 9200,
                    'persistent' => false,
                ),
                'Http:NotPersistent'
            ),
            array(
                array(
                    'transport' => 'Http',
                    'host' => 'localhost',
                    'port' => 9200,
                    'persistent' => true,
                ),
                'Http:Persistent'
            ),
            array(
                array(
                    'transport' => 'Thrift',
                    'host' => 'localhost',
                    'port' => 9500,
                    'framedProtocol' => true,
                ),
                'Thrift:Framed'
            ),
            array(
                array(
                    'transport' => 'Thrift',
                    'host' => 'localhost',
                    'port' => 9500,
                    'framedProtocol' => false,
                ),
                'Thrift:Binary'
            ),
        );
    }

    /**
     * @param string $test
     * @return array
     */
    protected function getData($test)
    {
        $data = array(
            'test' => $test,
            'name' => array(),
        );
        for ($i = 0; $i < $this->_maxData; $i++) {
            $data['name'][] = uniqid();
        }
        return $data;
    }

    /**
     * @param $name
     * @param $transport
     * @param array $times
     */
    protected function logResults($name, $transport, array $times)
    {
        self::$_results[$name][$transport] = array(
            'count' => count($times),
            'max'   => max($times) * 1000,
            'min'   => min($times) * 1000,
            'mean'  => (array_sum($times) / count($times)) * 1000,
        );
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
            $means = array();
            foreach ($values as $transport => $times) {
                $means[] = $times['mean'];
            }
            $minMean = min($means);
            foreach ($values as $transport => $times) {
                $perc = (($times['mean'] - $minMean) / $minMean) * 100;
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
}
