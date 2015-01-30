<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Document;
use Elastica\Test\Base as BaseTest;

/**
 * Tests the example code
 */
class ExampleTest extends BaseTest
{
    public function testBasicGettingStarted()
    {
        $client = new Client();
        $index = $client->getIndex('ruflin');
        $type = $index->getType('users');

        $id = 2;
        $data = array('firstname' => 'Nicolas', 'lastname' => 'Ruflin');
        $doc = new Document($id, $data);

        $type->addDocument($doc);
    }

    public function testExample()
    {
        // Creates a new index 'xodoa' and a type 'user' inside this index
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_test');
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

        $resultSet = $type->search('rolf');
    }
}
