<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Query\Terms;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class TermsTest extends BaseTest
{
    /**
     * @group unit
     */
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

    /**
     * @group unit
     */
    public function testInvalidParams(): void
    {
        $query = new Terms('field', ['aaa', 'bbb']);
        $query->setTermsLookup('index', '1', 'path');

        $this->expectException(InvalidException::class);
        $query->toArray();
    }

    /**
     * @group unit
     */
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

    /**
     * @group functional
     */
    public function testVariousDataTypesViaConstructor(): void
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document(1, ['some_numeric_field' => 9876]),
        ]);
        $index->refresh();

        // string
        $query = new Terms('some_numeric_field', ['9876']);
        $resultSet = $index->search($query);
        $this->assertEquals(1, $resultSet->count());

        // int
        $query = new Terms('some_numeric_field', [9876]);
        $resultSet = $index->search($query);
        $this->assertEquals(1, $resultSet->count());

        // float
        $query = new Terms('some_numeric_field', [9876.0]);
        $resultSet = $index->search($query);
        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testVariousMixedDataTypesViaConstructor(): void
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document(1, ['some_numeric_field' => 9876]),
            new Document(2, ['some_numeric_field' => 5678]),
            new Document(3, ['some_numeric_field' => 1234]),
            new Document(4, ['some_numeric_field' => 8899]),
        ]);
        $index->refresh();

        $query = new Terms('some_numeric_field', ['9876', 1234, 5678.0]);
        $resultSet = $index->search($query);
        $this->assertEquals(3, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testVariousDataTypesViaAddTerm(): void
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document(1, ['some_numeric_field' => 9876]),
        ]);
        $index->refresh();

        // string
        $query = new Terms('some_numeric_field');
        $query->addTerm('9876');
        $resultSet = $index->search($query);
        $this->assertEquals(1, $resultSet->count());

        // int
        $query = new Terms('some_numeric_field');
        $query->addTerm(9876);
        $resultSet = $index->search($query);
        $this->assertEquals(1, $resultSet->count());

        // float
        $query = new Terms('some_numeric_field');
        $query->addTerm(9876.0);
        $resultSet = $index->search($query);
        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @group unit
     */
    public function testAddTermTypeError(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 passed to "Elastica\Query\Terms::addTerm()" must be a scalar, stdClass given.');

        $query = new Terms('some_numeric_field');
        $query->addTerm(new \stdClass());
    }
}
