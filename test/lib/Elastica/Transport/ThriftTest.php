<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Transport_ThriftTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $host = 'localhost';
        $port = 9500;
        $client = new Elastica_Client(array('host' => $host, 'port' => $port, 'transport' => 'Thrift'));

        $this->assertEquals($host, $client->getHost());
        $this->assertEquals($port, $client->getPort());
    }

    /**
     * @dataProvider configProvider
     */
    public function testExample($config)
    {
        // Creates a new index 'xodoa' and a type 'user' inside this index
        $client = new Elastica_Client($config);

        $index = $client->getIndex('elastica_test1');
        $index->create(array(), true);

        $type = $index->getType('user');

        // Adds 1 document to the index
        $doc1 = new Elastica_Document(1,
            array('username' => 'hans', 'test' => array('2', '3', '5'))
        );
        $type->addDocument($doc1);

        // Adds a list of documents with _bulk upload to the index
        $docs = array();
        $docs[] = new Elastica_Document(2,
            array('username' => 'john', 'test' => array('1', '3', '6'))
        );
        $docs[] = new Elastica_Document(3,
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
            array(
                array('host' => 'localhost', 'port' => 9500, 'transport' => 'Thrift', 'framedProtocol' => true),
            ),
            array(
                array('host' => 'localhost', 'port' => 9500, 'transport' => 'Thrift', 'framedProtocol' => false),
            )
        );
    }
}
