<?php

namespace Elastica\Test\Transport;

use Elastica\Client;
use Elastica\Document;
use Elastica\Test\Base as BaseTest;

class MemcacheTest extends BaseTest
{
    public function setUp()
    {
        if (!extension_loaded('Memcache')) {
            $this->markTestSkipped('pecl/memcache must be installed to run this test case');
        }
    }

    public function testExample()
    {
        // Creates a new index 'xodoa' and a type 'user' inside this index
        $host = 'localhost';
        $port = 11211;
        $client = new Client(array('host' => $host, 'port' => $port, 'transport' => 'Memcache'));

        $index = $client->getIndex('elastica_test1');
        $index->create(array(), true);

        $type = $index->getType('user');

        // Adds 1 document to the index
        $doc1 = new Document(1,
            array('username' => 'hans', 'test' => array('2', '3', '5'))
        );
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
        $this->markTestIncomplete('Memcache implementation is not finished yet');
        $resultSet = $type->search('rolf');
    }
}
