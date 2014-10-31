<?php

namespace Elastica\Test\Transport;

use Elastica\Client;
use Elastica\Connection;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Test\Base as BaseTest;

class ThriftTest extends BaseTest
{
    public static function setUpBeforeClass()
    {
        if (!class_exists('Elasticsearch\\RestClient')) {
            self::markTestSkipped('munkie/elasticsearch-thrift-php package should be installed to run thrift transport tests');
        }
    }

    public function testConstruct()
    {
        $host = 'localhost';
        $port = 9500;
        $client = new Client(array('host' => $host, 'port' => $port, 'transport' => 'Thrift'));

        $this->assertEquals($host, $client->getConnection()->getHost());
        $this->assertEquals($port, $client->getConnection()->getPort());
    }

    /**
     * @dataProvider configProvider
     */
    public function testSearchRequest($config)
    {
        $this->_checkPlugin();

        // Creates a new index 'xodoa' and a type 'user' inside this index
        $client = new Client($config);

        $index = $client->getIndex('elastica_test1');
        $index->create(array(), true);

        $type = $index->getType('user');

        // Adds 1 document to the index
        $doc1 = new Document(1,
            array('username' => 'hans', 'test' => array('2', '3', '5'))
        );
        $doc1->setVersion(0);
        $type->addDocument($doc1);

        // Adds a list of documents with _bulk upload to the index
        $docs = array();
        $docs[] = new Document(2,
            array('username' => 'john', 'test' => array('1', '3', '6'))
        );
        $docs[] = new Document(3,
            array('username' => 'rolf', 'test' => array('2', '3', '7'))
        );
        $type->addDocuments($docs);

        // Refresh index
        $index->refresh();
        $resultSet = $type->search('rolf');

        $this->assertEquals(1, $resultSet->getTotalHits());
    }

    /**
     * @expectedException \Elastica\Exception\ConnectionException
     */
    public function testInvalidHostRequest()
    {
        $this->_checkPlugin();

        $client = new Client(array('host' => 'unknown', 'port' => 9555, 'transport' => 'Thrift'));
        $client->getStatus();
    }

    /**
     * @expectedException \Elastica\Exception\ResponseException
     */
    public function testInvalidElasticRequest()
    {
        $this->_checkPlugin();

        $connection = new Connection();
        $connection->setHost('localhost');
        $connection->setPort(9500);
        $connection->setTransport('Thrift');

        $client = new Client();
        $client->addConnection($connection);

        $index = new Index($client, 'missing_index');
        $index->getStatus();
    }

    public function configProvider()
    {
        return array(
            array(
                array(
                    'host' => 'localhost',
                    'port' => 9500,
                    'transport' => 'Thrift'
                )
            ),
            array(
                array(
                    'host' => 'localhost',
                    'port' => 9500,
                    'transport' => 'Thrift',
                    'config' => array(
                        'framedTransport' => false,
                        'sendTimeout' => 10000,
                        'recvTimeout' => 20000,
                    )
                )
            )
        );
    }

    protected function _checkPlugin()
    {
        $nodes = $this->_getClient()->getCluster()->getNodes();
        if (!$nodes[0]->getInfo()->hasPlugin('transport-thrift')) {
            $this->markTestSkipped("transport-thrift plugin not installed.");
        }
    }
}
