<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Result;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class ResultSetTest extends BaseTest
{
    #[Group('functional')]
    public function testGetters(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document('1', ['name' => 'elastica search']),
            new Document('2', ['name' => 'elastica library']),
            new Document('3', ['name' => 'elastica test']),
        ]);
        $index->refresh();

        $resultSet = $index->search('elastica search');

        $this->assertEquals(3, $resultSet->getTotalHits());
        $this->assertEquals('eq', $resultSet->getTotalHitsRelation());
        $this->assertGreaterThan(0, $resultSet->getMaxScore());
        $this->assertNotTrue($resultSet->hasTimedOut());
        $this->assertNotTrue($resultSet->hasAggregations());
        $this->assertNotTrue($resultSet->hasSuggests());
        $this->assertIsArray($resultSet->getResults());
        $this->assertNull($resultSet->getPointInTimeId());
        $this->assertCount(3, $resultSet);
    }

    #[Group('functional')]
    public function testArrayAccess(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document('1', ['name' => 'elastica search']),
            new Document('2', ['name' => 'elastica library']),
            new Document('3', ['name' => 'elastica test']),
        ]);
        $index->refresh();

        $resultSet = $index->search('elastica search');

        $this->assertContainsOnlyInstancesOf(Result::class, $resultSet);
        $this->assertArrayNotHasKey(3, $resultSet);
    }

    #[Group('functional')]
    public function testDocumentsAccess(): void
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document('1', ['name' => 'elastica search']),
            new Document('2', ['name' => 'elastica library']),
            new Document('3', ['name' => 'elastica test']),
        ]);
        $index->refresh();

        $resultSet = $index->search('elastica search');
        $documents = $resultSet->getDocuments();

        $this->assertIsArray($documents);
        $this->assertCount(3, $documents);
        $this->assertContainsOnlyInstancesOf(Document::class, $documents);
        $this->assertArrayNotHasKey(3, $documents);
        $this->assertEquals('elastica search', $documents[0]->get('name'));
    }

    #[Group('functional')]
    public function testInvalidOffsetCreation(): void
    {
        $this->expectException(InvalidException::class);

        $index = $this->_createIndex();
        $index->addDocument(new Document('1', ['name' => 'elastica search']));
        $index->refresh();

        $resultSet = $index->search('elastica search');
        $resultSet[1] = new Result(['_id' => 'fakeresult']);
    }

    #[Group('functional')]
    public function testInvalidOffsetGet(): void
    {
        $this->expectException(InvalidException::class);

        $index = $this->_createIndex();

        $doc = new Document('1', ['name' => 'elastica search']);
        $index->addDocument($doc);
        $index->refresh();

        $index->search('elastica search')[3];
    }
}
