<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\Percolate;
use Elastica\Test\Base as BaseTest;

class PercolateTest extends BaseTest
{
    /**
     * @var \Elastica\Index
     */
    private $index;

    private $documentType;

    private $queriesType;

    /**
     * @group functional
     */
    public function testPercolateQueryOnNewDocument()
    {
        $this->_prepareIndexForPercolate();
        //Register a query in the percolator:
        $queryDoc = new Document(1, ['query' => ['match' => ['message' => 'bonsai tree']]]);
        $doc = new Document(2, ['message' => 'A new bonsai tree in the office']);
        $this->queriesType->addDocument($queryDoc);
        $this->index->refresh();
        //Match a document to the registered percolator queries:
        $percolateQuery = new Percolate();
        $percolateQuery->setField('query')
            ->setDocument($doc->getData())
            ->setDocumentType('doctype');
        $resultSet = $this->index->search($percolateQuery);

        $this->assertEquals(1, $resultSet->count());

        //Register a new query in the percolator:
        $queryDoc = new Document(3, ['query' => ['match' => ['message' => 'i have nice bonsai tree']]]);
        $this->queriesType->addDocument($queryDoc);
        $this->index->refresh();
        $resultSet = $this->index->search($percolateQuery);

        //Match a document to the registered percolator queries:
        $this->assertEquals(2, $resultSet->count());

        //Check on the document without keywords from percolate stored query
        $doc2 = new Document(4, ['message' => 'Just a simple text for test']);
        $percolateQuery = new Percolate();
        $percolateQuery->setField('query')
            ->setDocument($doc2->getData())
            ->setDocumentType('doctype');
        $resultSet = $this->index->search($percolateQuery);

        $this->assertEquals(0, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testPercolateQueryOnExistingDocument()
    {
        $this->_prepareIndexForPercolate();
        //Register a query in the percolator:
        $queryDoc = new Document(1, ['query' => ['match' => ['message' => 'bonsai tree']]]);
        $doc = new Document(2, ['message' => 'A new bonsai tree in the office']);
        $this->queriesType->addDocument($queryDoc);
        $this->documentType->addDocument($doc);
        $this->index->refresh();

        $percolateQuery = new Percolate();
        $percolateQuery->setField('query')
            ->setDocumentType('doctype')
            ->setExistingDocumentType($this->documentType->getName())
            ->setDocumentIndex($this->documentType->getIndex()->getName())
            ->setDocumentId($doc->getId());
        $resultSet = $this->index->search($percolateQuery);

        $this->assertEquals(1, $resultSet->count());

        $queryDoc = new Document(3, ['query' => ['match' => ['message' => 'i have nice bonsai tree']]]);
        $this->queriesType->addDocument($queryDoc);
        $this->index->refresh();
        $resultSet = $this->index->search($percolateQuery);

        $this->assertEquals(2, $resultSet->count());

        $doc2 = new Document(4, ['message' => 'Just a simple text for test']);
        $this->documentType->addDocument($doc2);
        $percolateQuery = new Percolate();
        $percolateQuery->setField('query')
            ->setDocumentType('doctype')
            ->setExistingDocumentType($this->documentType->getName())
            ->setDocumentIndex($this->documentType->getIndex()->getName())
            ->setDocumentId($doc2->getId());
        $resultSet = $this->index->search($percolateQuery);

        $this->assertEquals(0, $resultSet->count());
    }

    private function _prepareIndexForPercolate()
    {
        //Create an index with two mappings:
        $this->index = $this->_createIndex();
        $this->index->getSettings()->setNumberOfReplicas(0);
        //The doctype mapping is the mapping used to preprocess the document
        // defined in the percolator query before it gets indexed into a temporary index.
        $this->documentType = $this->index->getType('doctype');
        //The queries mapping is the mapping used for indexing the query documents.
        $this->queriesType = $this->index->getType('queries');
        $this->documentType->setMapping(['message' => ['type' => 'text']]);
        $this->queriesType->setMapping(['query' => ['type' => 'percolator']]);
    }

    /**
     * @group unit
     */
    public function testSetField()
    {
        $field = 'field1';
        $query = new Percolate();
        $query->setField($field);

        $data = $query->toArray();

        $this->assertEquals($data['percolate']['field'], $field);
    }

    /**
     * @group unit
     */
    public function testSetDocumentType()
    {
        $type = 'type';
        $query = new Percolate();
        $query->setDocumentType($type);

        $data = $query->toArray();

        $this->assertEquals($data['percolate']['document_type'], $type);
    }

    /**
     * @group unit
     */
    public function testSetDocument()
    {
        $query = new Percolate();
        $doc = new Document(1, ['message' => 'A new bonsai tree in the office']);
        $query->setDocument($doc->getData());

        $data = $query->toArray();

        $this->assertEquals($data['percolate']['document'], $doc->getData());
    }

    /**
     * @group unit
     */
    public function testSetDocumentIndex()
    {
        $index = $this->_createIndex('indexone');
        $query = new Percolate();
        $query->setDocumentIndex($index->getName());

        $data = $query->toArray();

        $this->assertEquals($data['percolate']['index'], $index->getName());
    }

    /**
     * @group unit
     */
    public function testSetExistingDocumentType()
    {
        $type = 'newType';
        $query = new Percolate();
        $query->setExistingDocumentType($type);

        $data = $query->toArray();

        $this->assertEquals($data['percolate']['type'], $type);
    }

    /**
     * @group unit
     */
    public function testSetDocumentId()
    {
        $id = 3;
        $query = new Percolate();
        $query->setDocumentId($id);

        $data = $query->toArray();

        $this->assertEquals($data['percolate']['id'], $id);
    }

    /**
     * @group unit
     */
    public function testSetDocumentRouting()
    {
        $routing = 'testRout';
        $query = new Percolate();
        $query->setDocumentRouting($routing);

        $data = $query->toArray();

        $this->assertEquals($data['percolate']['routing'], $routing);
    }

    /**
     * @group unit
     */
    public function testSetDocumentPreference()
    {
        $preference = ['pref1' => 'test', 'pref2' => 'test2'];
        $query = new Percolate();
        $query->setDocumentPreference($preference);

        $data = $query->toArray();

        $this->assertEquals($data['percolate']['preference'], $preference);
    }

    /**
     * @group unit
     */
    public function testSetDocumentVersion()
    {
        $version = 10;
        $query = new Percolate();
        $query->setDocumentVersion($version);

        $data = $query->toArray();

        $this->assertEquals($data['percolate']['version'], $version);
    }
}
