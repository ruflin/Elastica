<?php
namespace Elastica\Test;

use Elastica\Document;
use Elastica\Test\Base as BaseTest;

/**
 * Tests the example code.
 */
class ExampleTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testBasicGettingStarted()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('ruflin');
        $type = $index->getType('users');

        $id = 2;
        $data = ['firstname' => 'Nicolas', 'lastname' => 'Ruflin'];
        $doc = new Document($id, $data);

        $type->addDocument($doc);
    }

    /**
     * @group functional
     */
    public function testExample()
    {
        // Creates a new index 'xodoa' and a type 'user' inside this index
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_test');
        $index->create([], true);

        $type = $index->getType('user');

        // Adds 1 document to the index
        $doc1 = new Document(1,
            ['username' => 'hans', 'test' => ['2', '3', '5']]
        );
        $type->addDocument($doc1);

        // Adds a list of documents with _bulk upload to the index
        $docs = [];
        $docs[] = new Document(2,
            ['username' => 'john', 'test' => ['1', '3', '6']]
        );
        $docs[] = new Document(3,
            ['username' => 'rolf', 'test' => ['2', '3', '7']]
        );
        $type->addDocuments($docs);

        // Refresh index
        $index->refresh();

        $resultSet = $type->search('rolf');
    }
}
