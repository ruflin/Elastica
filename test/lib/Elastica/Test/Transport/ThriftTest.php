<?php

namespace Elastica\Test\Transport;

use Elastica\Client;
use Elastica\Connection;
use Elastica\Document;
use Elastica\Query;
use Elastica\Test\Base as BaseTest;

class ThriftTest extends BaseTest
{
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
    public function testExample($config)
    {
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

    public function configProvider()
    {
        return array(
            /*
            array(
                array(
                    'host' => 'localhost',
                    'port' => 9500,
                    'transport' => 'Thrift',
                    'config' => array(
                        'framedProtocol' => true
                    )
                ),
            ),
            */
            array(
                array(
                    'host' => 'localhost',
                    'port' => 9500,
                    'transport' => 'Thrift',
                    'config' => array(
                        'framedProtocol' => false,
                        'sendTimeout' => 10000,
                        'recvTimeout' => 20000,
                    )
                )
            )
        );
    }
}
