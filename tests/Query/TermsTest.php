<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Query\Terms;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 * @group unit
 */
class TermsTest extends BaseTest
{
    public function testSetTermsLookup(): void
    {
        $terms = [
            'index' => 'index_name',
            'id' => '1',
            'path' => 'terms',
        ];

        $query = new Terms('name');
        $query->setTermsLookup('index_name', '1', 'terms');

        $data = $query->toArray();
        $this->assertEquals($terms, $data['terms']['name']);
    }

    public function testInvalidParams(): void
    {
        $query = new Terms('field', ['aaa', 'bbb']);
        $query->setTermsLookup('index', '1', 'path');

        $this->expectException(InvalidException::class);
        $query->toArray();
    }

    public function testEmptyField(): void
    {
        $this->expectException(InvalidException::class);
        new Terms('');
    }

    /**
     * @group functional
     */
    public function testFilteredSearch(): void
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document(1, ['name' => 'hello world']),
            new Document(2, ['name' => 'nicolas ruflin']),
            new Document(3, ['name' => 'ruflin']),
        ]);

        $query = new Terms('name', ['nicolas', 'hello']);

        $index->refresh();
        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());

        $query->addTerm('ruflin');
        $resultSet = $index->search($query);

        $this->assertEquals(3, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testFilteredSearchWithLookup(): void
    {
        $index = $this->_createIndex();

        $lookupIndex = $this->_createIndex('lookup_index');
        $lookupIndex->addDocuments([
            new Document(1, ['terms' => ['ruflin', 'nicolas']]),
        ]);

        $index->addDocuments([
            new Document(1, ['name' => 'hello world']),
            new Document(2, ['name' => 'nicolas ruflin']),
            new Document(3, ['name' => 'ruflin']),
        ]);

        $query = new Terms('name');
        $query->setTermsLookup($lookupIndex->getName(), '1', 'terms');
        $index->refresh();
        $lookupIndex->refresh();

        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());
    }
}
