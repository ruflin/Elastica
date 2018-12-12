<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Result;
use Elastica\ResultSet;
use Elastica\Test\Base as BaseTest;

class ResultSetTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testGetters()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        $type->addDocuments([
            new Document(1, ['name' => 'elastica search']),
            new Document(2, ['name' => 'elastica library']),
            new Document(3, ['name' => 'elastica test']),
        ]);
        $index->refresh();

        $resultSet = $type->search('elastica search');

        $this->assertInstanceOf(ResultSet::class, $resultSet);
        $this->assertEquals(3, $resultSet->getTotalHits());
        $this->assertGreaterThan(0, $resultSet->getMaxScore());
        $this->assertNotTrue($resultSet->hasTimedOut());
        $this->assertNotTrue($resultSet->hasAggregations());
        $this->assertNotTrue($resultSet->hasSuggests());
        $this->assertInternalType('array', $resultSet->getResults());
        $this->assertCount(3, $resultSet);
    }

    /**
     * @group functional
     */
    public function testArrayAccess()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        $type->addDocuments([
            new Document(1, ['name' => 'elastica search']),
            new Document(2, ['name' => 'elastica library']),
            new Document(3, ['name' => 'elastica test']),
        ]);
        $index->refresh();

        $resultSet = $type->search('elastica search');

        $this->assertInstanceOf(ResultSet::class, $resultSet);
        $this->assertInstanceOf(Result::class, $resultSet[0]);
        $this->assertInstanceOf(Result::class, $resultSet[1]);
        $this->assertInstanceOf(Result::class, $resultSet[2]);

        $this->assertArrayNotHasKey(3, $resultSet);
    }

    /**
     * @group functional
     */
    public function testDocumentsAccess()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        $type->addDocuments([
            new Document(1, ['name' => 'elastica search']),
            new Document(2, ['name' => 'elastica library']),
            new Document(3, ['name' => 'elastica test']),
        ]);
        $index->refresh();

        $resultSet = $type->search('elastica search');

        $this->assertInstanceOf(ResultSet::class, $resultSet);

        $documents = $resultSet->getDocuments();

        $this->assertInternalType('array', $documents);
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
    public function testInvalidOffsetCreation()
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        $doc = new Document(1, ['name' => 'elastica search']);
        $type->addDocument($doc);
        $index->refresh();

        $resultSet = $type->search('elastica search');

        $result = new Result(['_id' => 'fakeresult']);
        $resultSet[1] = $result;
    }

    /**
     * @group functional
     */
    public function testInvalidOffsetGet()
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        $doc = new Document(1, ['name' => 'elastica search']);
        $type->addDocument($doc);
        $index->refresh();

        $resultSet = $type->search('elastica search');

        return $resultSet[3];
    }
}
