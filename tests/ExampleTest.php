<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Test\Base as BaseTest;

/**
 * Tests the example code.
 *
 * @internal
 */
class ExampleTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testBasicGettingStarted(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('ruflin');

        $id = 2;
        $data = ['firstname' => 'Nicolas', 'lastname' => 'Ruflin'];
        $doc = new Document($id, $data);

        $index->addDocument($doc);
    }

    /**
     * @group functional
     */
    public function testExample(): void
    {
        // Creates a new index 'xodoa' and a type 'user' inside this index
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_test');
        $index->create([], true);

        // Adds 1 document to the index
        $index->addDocument(new Document(1, ['username' => 'hans', 'test' => ['2', '3', '5']]));

        // Adds a list of documents with _bulk upload to the index
        $index->addDocuments([
            new Document(2, ['username' => 'john', 'test' => ['1', '3', '6']]),
            new Document(3, ['username' => 'rolf', 'test' => ['2', '3', '7']]),
        ]);

        // Refresh index
        $index->refresh();

        $resultSet = $index->search('rolf');
    }
}
