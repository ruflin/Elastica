<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Mapping;
use Elastica\Result;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class ResultTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testGetters(): void
    {
        // Creates a new index 'xodoa'
        $index = $this->_createIndex();
        $index->addDocument(new Document(3, ['username' => 'hans']));
        $index->refresh();

        $resultSet = $index->search('hans');

        $this->assertEquals(1, $resultSet->count());

        $result = $resultSet->current();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertInstanceOf(Document::class, $result->getDocument());
        $this->assertEquals($index->getName(), $result->getIndex());
        $this->assertEquals(3, $result->getId());
        $this->assertGreaterThan(0, $result->getScore());
        $this->assertIsArray($result->getData());
        $this->assertTrue(isset($result->username));
        $this->assertEquals('hans', $result->username);
    }

    /**
     * @group functional
     */
    public function testGetIdNoSource(): void
    {
        // Creates a new index 'xodoa'
        $indexName = 'xodoa';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create([], true);

        $mapping = new Mapping();
        $mapping->disableSource();
        $index->setMapping($mapping);

        // Adds 1 document to the index
        $docId = 3;
        $doc1 = new Document($docId, ['username' => 'hans']);
        $index->addDocument($doc1);

        // Refreshes index
        $index->refresh();

        $resultSet = $index->search('hans');

        $this->assertEquals(1, $resultSet->count());

        $result = $resultSet->current();

        $this->assertEquals([], $result->getSource());
        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($indexName, $result->getIndex());
        $this->assertEquals($docId, $result->getId());
        $this->assertGreaterThan(0, $result->getScore());
        $this->assertIsArray($result->getData());
    }

    /**
     * @group functional
     */
    public function testGetTotalTimeReturnsExpectedResults(): void
    {
        $index = $this->_createIndex();

        // Adds 1 document to the index
        $docId = 3;
        $doc1 = new Document($docId, ['username' => 'hans']);
        $index->addDocument($doc1);

        // Refreshes index
        $index->refresh();

        $resultSet = $index->search('hans');

        $this->assertNotNull($resultSet->getTotalTime(), 'Get Total Time should never be a null value');
        $this->assertEquals(
            'integer',
            \gettype($resultSet->getTotalTime()),
            'Total Time should be an integer'
        );
    }

    /**
     * @group unit
     */
    public function testHasFields(): void
    {
        $data = ['value set'];

        $result = new Result([]);
        $this->assertFalse($result->hasFields());

        $result = new Result(['_source' => $data]);
        $this->assertFalse($result->hasFields());

        $result = new Result(['fields' => $data]);
        $this->assertTrue($result->hasFields());
        $this->assertEquals($data, $result->getFields());
    }
}
