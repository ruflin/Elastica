<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Result;
use Elastica\ResultSet;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class ResultSetTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testGetters(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document(1, ['name' => 'elastica search']),
            new Document(2, ['name' => 'elastica library']),
            new Document(3, ['name' => 'elastica test']),
        ]);
        $index->refresh();

        $resultSet = $index->search('elastica search');

        $this->assertInstanceOf(ResultSet::class, $resultSet);
        $this->assertEquals(3, $resultSet->getTotalHits());
        $this->assertEquals('eq', $resultSet->getTotalHitsRelation());
        $this->assertGreaterThan(0, $resultSet->getMaxScore());
        $this->assertNotTrue($resultSet->hasTimedOut());
        $this->assertNotTrue($resultSet->hasAggregations());
        $this->assertNotTrue($resultSet->hasSuggests());
        $this->assertIsArray($resultSet->getResults());
        $this->assertCount(3, $resultSet);
    }

    /**
     * @group functional
     */
    public function testArrayAccess(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document(1, ['name' => 'elastica search']),
            new Document(2, ['name' => 'elastica library']),
            new Document(3, ['name' => 'elastica test']),
        ]);
        $index->refresh();

        $resultSet = $index->search('elastica search');

        $this->assertInstanceOf(ResultSet::class, $resultSet);
        $this->assertInstanceOf(Result::class, $resultSet[0]);
        $this->assertInstanceOf(Result::class, $resultSet[1]);
        $this->assertInstanceOf(Result::class, $resultSet[2]);

        $this->assertArrayNotHasKey(3, $resultSet);
    }

    /**
     * @group functional
     */
    public function testDocumentsAccess(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document(1, ['name' => 'elastica search']),
            new Document(2, ['name' => 'elastica library']),
            new Document(3, ['name' => 'elastica test']),
        ]);
        $index->refresh();

        $resultSet = $index->search('elastica search');

        $this->assertInstanceOf(ResultSet::class, $resultSet);

        $documents = $resultSet->getDocuments();

        $this->assertIsArray($documents);
        $this->assertCount(3, $documents);
        $this->assertInstanceOf(Document::class, $documents[0]);
        $this->assertInstanceOf(Document::class, $documents[1]);
        $this->assertInstanceOf(Document::class, $documents[2]);
        $this->assertArrayNotHasKey(3, $documents);
        $this->assertEquals('elastica search', $documents[0]->get('name'));
    }

    /**
     * @group functional
     */
    public function testInvalidOffsetCreation(): void
    {
        $this->expectException(InvalidException::class);

        $index = $this->_createIndex();
        $index->addDocument(new Document(1, ['name' => 'elastica search']));
        $index->refresh();

        $resultSet = $index->search('elastica search');

        $result = new Result(['_id' => 'fakeresult']);
        $resultSet[1] = $result;
    }

    /**
     * @group functional
     */
    public function testInvalidOffsetGet()
    {
        $this->expectException(InvalidException::class);

        $index = $this->_createIndex();

        $doc = new Document(1, ['name' => 'elastica search']);
        $index->addDocument($doc);
        $index->refresh();

        $resultSet = $index->search('elastica search');

        return $resultSet[3];
    }
}
