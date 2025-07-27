<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Client;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query\Percolate;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class PercolateTest extends BaseTest
{
    /**
     * @var Index
     */
    private $index;

    #[Group('functional')]
    public function testPercolateQueryOnNewDocument(): void
    {
        $this->_prepareIndexForPercolate();
        // Register a query in the percolator:
        $queryDoc = new Document('1', ['query' => ['match' => ['message' => 'bonsai tree']]]);
        $doc = new Document('2', ['message' => 'A new bonsai tree in the office']);
        $this->index->addDocument($queryDoc);
        $this->index->refresh();
        // Match a document to the registered percolator queries:
        $percolateQuery = new Percolate();
        $percolateQuery->setField('query')
            ->setDocument($doc->getData())
        ;
        $resultSet = $this->index->search($percolateQuery);

        $this->assertEquals(1, $resultSet->count());

        // Register a new query in the percolator:
        $queryDoc = new Document('3', ['query' => ['match' => ['message' => 'i have nice bonsai tree']]]);
        $this->index->addDocument($queryDoc);
        $this->index->refresh();
        $resultSet = $this->index->search($percolateQuery);

        // Match a document to the registered percolator queries:
        $this->assertEquals(2, $resultSet->count());

        // Check on the document without keywords from percolate stored query
        $doc2 = new Document('4', ['message' => 'Just a simple text for test']);
        $percolateQuery = new Percolate();
        $percolateQuery->setField('query')
            ->setDocument($doc2->getData())
        ;
        $resultSet = $this->index->search($percolateQuery);

        $this->assertEquals(0, $resultSet->count());
    }

    #[Group('functional')]
    public function testPercolateQueryOnExistingDocument(): void
    {
        $this->_prepareIndexForPercolate();
        // Register a query in the percolator:
        $queryDoc = new Document('1', ['query' => ['match' => ['message' => 'bonsai tree']]]);
        $doc = new Document('2', ['message' => 'A new bonsai tree in the office']);
        $this->index->addDocument($doc);
        $this->index->addDocument($queryDoc);
        $this->index->refresh();

        $percolateQuery = new Percolate();
        $percolateQuery->setField('query')
            ->setDocumentIndex($this->index->getName())
            ->setDocumentId($doc->getId())
        ;
        $resultSet = $this->index->search($percolateQuery);

        $this->assertEquals(1, $resultSet->count());

        $queryDoc = new Document('3', ['query' => ['match' => ['message' => 'i have nice bonsai tree']]]);
        $this->index->addDocument($queryDoc);
        $this->index->refresh();
        $resultSet = $this->index->search($percolateQuery);

        $this->assertEquals(2, $resultSet->count());

        $doc2 = new Document('4', ['message' => 'Just a simple text for test']);
        $this->index->addDocument($doc2);
        $percolateQuery = new Percolate();
        $percolateQuery->setField('query')
            ->setDocumentIndex($this->index->getName())
            ->setDocumentId($doc2->getId())
        ;
        $resultSet = $this->index->search($percolateQuery);

        $this->assertEquals(0, $resultSet->count());
    }

    #[Group('unit')]
    public function testSetField(): void
    {
        $field = 'field1';
        $query = new Percolate();
        $query->setField($field);

        $data = $query->toArray();

        $this->assertEquals($data['percolate']['field'], $field);
    }

    #[Group('unit')]
    public function testSetDocument(): void
    {
        $query = new Percolate();
        $doc = new Document('1', ['message' => 'A new bonsai tree in the office']);
        $query->setDocument($doc->getData());

        $data = $query->toArray();

        $this->assertEquals($data['percolate']['document'], $doc->getData());
    }

    #[Group('unit')]
    public function testSetDocumentIndex(): void
    {
        $client = $this->createMock(Client::class);
        $index = new Index($client, 'indexone');
        $query = new Percolate();
        $query->setDocumentIndex($index->getName());

        $data = $query->toArray();

        $this->assertEquals($data['percolate']['index'], $index->getName());
    }

    #[Group('unit')]
    public function testSetDocumentId(): void
    {
        $id = 3;
        $query = new Percolate();
        $query->setDocumentId($id);

        $data = $query->toArray();

        $this->assertEquals($data['percolate']['id'], $id);
    }

    #[Group('unit')]
    public function testSetDocumentRouting(): void
    {
        $routing = 'testRout';
        $query = new Percolate();
        $query->setDocumentRouting($routing);

        $data = $query->toArray();

        $this->assertEquals($data['percolate']['routing'], $routing);
    }

    #[Group('unit')]
    public function testSetDocumentPreference(): void
    {
        $preference = ['pref1' => 'test', 'pref2' => 'test2'];
        $query = new Percolate();
        $query->setDocumentPreference($preference);

        $data = $query->toArray();

        $this->assertEquals($data['percolate']['preference'], $preference);
    }

    #[Group('unit')]
    public function testSetDocumentVersion(): void
    {
        $version = 10;
        $query = new Percolate();
        $query->setDocumentVersion($version);

        $data = $query->toArray();

        $this->assertEquals($data['percolate']['version'], $version);
    }

    private function _prepareIndexForPercolate(): void
    {
        $this->index = $this->_createIndex();
        $this->index->getSettings()->setNumberOfReplicas(0);
        // The doctype mapping is the mapping used to preprocess the document
        // defined in the percolator query before it gets indexed into a temporary index.
        // The queries mapping is the mapping used for indexing the query documents.
        $this->index->setMapping(new Mapping(['message' => ['type' => 'text'], 'query' => ['type' => 'percolator']]));
    }
}
