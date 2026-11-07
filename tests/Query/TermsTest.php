<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Query\Terms;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class TermsTest extends BaseTest
{
    #[DataProvider('nonListTermsProvider')]
    #[Group('unit')]
    public function testConstructorRejectsNonListTerms(array $terms): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('Terms must be a list. Use array_values() to reindex the terms.');

        new Terms('name', $terms);
    }

    #[DataProvider('nonListTermsProvider')]
    #[Group('unit')]
    public function testSetTermsRejectsNonListTerms(array $terms): void
    {
        $query = new Terms('name', ['original']);

        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('Terms must be a list. Use array_values() to reindex the terms.');

        try {
            $query->setTerms($terms);
        } finally {
            $this->assertSame(['terms' => ['name' => ['original']]], $query->toArray());
        }
    }

    public static function nonListTermsProvider(): iterable
    {
        yield 'removed first term' => [[1 => 'foo', 2 => 'bar']];
        yield 'gap between terms' => [[0 => 'foo', 2 => 'bar']];
        yield 'out of order keys' => [[1 => 'foo', 0 => 'bar']];
        yield 'string keys' => [['first' => 'foo', 'second' => 'bar']];
    }

    #[DataProvider('listTermsProvider')]
    #[Group('unit')]
    public function testListTerms(array $terms): void
    {
        $expected = ['terms' => ['name' => $terms]];
        $this->assertSame($expected, (new Terms('name', $terms))->toArray());

        $query = (new Terms('name'))->setTermsLookup('index_name', '1', 'terms');
        $this->assertSame($query, $query->setTerms($terms));
        $this->assertSame($expected, $query->toArray());
    }

    public static function listTermsProvider(): iterable
    {
        yield 'empty list' => [[]];
        yield 'scalar types and duplicates' => [[true, false, 1, 1.5, 'foo', 'foo']];
        yield 'reindexed terms' => [\array_values([1 => 'foo', 2 => 'bar'])];
    }

    #[Group('unit')]
    public function testSetTermsLookup(): void
    {
        $expected = [
            'terms' => [
                'name' => [
                    'index' => 'index_name',
                    'id' => '1',
                    'path' => 'terms',
                ],
            ],
        ];

        $query = (new Terms('name'))
            ->setTermsLookup('index_name', '1', 'terms')
        ;

        $this->assertSame($expected, $query->toArray());
    }

    #[Group('unit')]
    public function testSetBoost(): void
    {
        $expected = [
            'terms' => [
                'name' => ['foo', 'bar'],
                'boost' => 2.0,
            ],
        ];

        $query = (new Terms('name', ['foo', 'bar']))
            ->setBoost(2.0)
        ;

        $this->assertSame($expected, $query->toArray());
    }

    #[Group('unit')]
    public function testInvalidParams(): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('Mixed terms and terms lookup are not allowed.');

        (new Terms('field', ['aaa', 'bbb']))
            ->setTermsLookup('index', '1', 'path')
            ->addTerm('ccc')
        ;
    }

    #[Group('unit')]
    public function testEmptyField(): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('Terms field name has to be set');

        new Terms('');
    }

    #[Group('functional')]
    public function testFilteredSearch(): void
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['name' => 'hello world']),
            new Document('2', ['name' => 'nicolas ruflin']),
            new Document('3', ['name' => 'ruflin']),
        ]);

        $query = new Terms('name', ['nicolas', 'hello']);

        $index->refresh();
        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());

        $query->addTerm('ruflin');
        $resultSet = $index->search($query);

        $this->assertEquals(3, $resultSet->count());
    }

    #[Group('functional')]
    public function testFilteredSearchWithLookup(): void
    {
        $index = $this->_createIndex();

        $lookupIndex = $this->_createIndex('lookup_index');
        $lookupIndex->addDocuments([
            new Document('1', ['terms' => ['ruflin', 'nicolas']]),
        ]);

        $index->addDocuments([
            new Document('1', ['name' => 'hello world']),
            new Document('2', ['name' => 'nicolas ruflin']),
            new Document('3', ['name' => 'ruflin']),
        ]);

        $query = new Terms('name');
        $query->setTermsLookup($lookupIndex->getName(), '1', 'terms');
        $index->refresh();
        $lookupIndex->refresh();

        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());
    }

    #[Group('functional')]
    public function testVariousDataTypesViaConstructor(): void
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['some_numeric_field' => 9876]),
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

    #[Group('functional')]
    public function testVariousMixedDataTypesViaConstructor(): void
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['some_numeric_field' => 9876]),
            new Document('2', ['some_numeric_field' => 5678]),
            new Document('3', ['some_numeric_field' => 1234]),
            new Document('4', ['some_numeric_field' => 8899]),
        ]);
        $index->refresh();

        $query = new Terms('some_numeric_field', ['9876', 1234, 5678.0]);
        $resultSet = $index->search($query);
        $this->assertEquals(3, $resultSet->count());
    }

    #[Group('functional')]
    public function testVariousDataTypesViaAddTerm(): void
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['some_numeric_field' => 9876]),
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

    #[Group('unit')]
    public function testAddTermTypeError(): void
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 1 passed to "Elastica\Query\Terms::addTerm()" must be a scalar, stdClass given.');

        $query = new Terms('some_numeric_field');
        $query->addTerm(new \stdClass());
    }
}
