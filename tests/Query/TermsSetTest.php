<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Query\TermsSet;
use Elastica\Script\Script;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class TermsSetTest extends BaseTest
{
    #[Group('unit')]
    public function testMinimumShouldMatchField(): void
    {
        $expected = [
            'terms_set' => [
                'field' => [
                    'terms' => ['foo', 'bar'],
                    'minimum_should_match_field' => 'match_field',
                ],
            ],
        ];

        $query = new TermsSet('field', ['foo', 'bar'], 'match_field');

        $this->assertSame($expected, $query->toArray());
    }

    #[Group('unit')]
    public function testMinimumShouldMatchNumber(): void
    {
        $expected = [
            'terms_set' => [
                'field' => [
                    'terms' => ['foo', 'bar'],
                    'minimum_should_match' => 1,
                ],
            ],
        ];

        $query = new TermsSet('field', ['foo', 'bar'], 1);

        $this->assertSame($expected, $query->toArray());
    }

    #[Group('unit')]
    public function testEmptyField(): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('TermsSet field name has to be set');

        new TermsSet('', ['foo', 'bar'], 'match_field');
    }

    #[DataProvider('nonListTermsProvider')]
    #[Group('unit')]
    public function testConstructorRejectsNonListTerms(array $terms): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('Terms must be a list. Use array_values() to reindex the terms.');

        new TermsSet('field', $terms, 1);
    }

    #[DataProvider('nonListTermsProvider')]
    #[Group('unit')]
    public function testSetTermsRejectsNonListTerms(array $terms): void
    {
        $query = new TermsSet('field', ['original'], 1);

        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('Terms must be a list. Use array_values() to reindex the terms.');

        try {
            $query->setTerms($terms);
        } finally {
            $this->assertSame([
                'terms_set' => [
                    'field' => [
                        'terms' => ['original'],
                        'minimum_should_match' => 1,
                    ],
                ],
            ], $query->toArray());
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
        $expected = [
            'terms_set' => [
                'field' => [
                    'terms' => $terms,
                    'minimum_should_match' => 1,
                ],
            ],
        ];

        $this->assertSame($expected, (new TermsSet('field', $terms, 1))->toArray());

        $query = new TermsSet('field', ['initial'], 1);
        $this->assertSame($query, $query->setTerms($terms));
        $this->assertSame($expected, $query->toArray());
    }

    public static function listTermsProvider(): iterable
    {
        yield 'empty list' => [[]];
        yield 'scalar types and duplicates' => [[true, false, 1, 1.5, 'foo', 'foo']];
        yield 'reindexed terms' => [\array_values([1 => 'foo', 2 => 'bar'])];
    }

    #[Group('functional')]
    public function testMinimumShouldMatchScriptSearch(): void
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['skills' => ['php', 'js']]),
            new Document('2', ['skills' => ['php']]),
            new Document('3', ['skills' => ['java']]),
        ]);

        $index->refresh();

        $query = new TermsSet('skills', ['php'], new Script('1'));
        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());

        $query = new TermsSet('skills', ['php', 'java'], new Script('1'));
        $resultSet = $index->search($query);

        $this->assertEquals(3, $resultSet->count());

        $query = new TermsSet('skills', ['php', 'js'], new Script('2'));
        $resultSet = $index->search($query);

        $this->assertEquals(1, $resultSet->count());

        $query = new TermsSet('skills', ['php', 'java'], new Script('2'));
        $resultSet = $index->search($query);

        $this->assertEquals(0, $resultSet->count());
    }

    #[Group('functional')]
    public function testMinimumShouldMatchFieldSearch(): void
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['skill_count' => 2, 'skills' => ['php', 'js']]),
            new Document('2', ['skill_count' => 1, 'skills' => ['php']]),
            new Document('3', ['skill_count' => 1, 'skills' => ['java']]),
        ]);

        $index->refresh();

        $query = new TermsSet('skills', ['php'], 'skill_count');
        $resultSet = $index->search($query);

        $this->assertEquals(1, $resultSet->count());

        $query = new TermsSet('skills', ['php', 'java'], 'skill_count');
        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());

        $query = new TermsSet('skills', ['php', 'js'], 'skill_count');
        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());

        $query = new TermsSet('skills', ['js', 'c++'], 'skill_count');
        $resultSet = $index->search($query);

        $this->assertEquals(0, $resultSet->count());
    }

    #[Group('functional')]
    public function testMinimumShouldMatchNumberSearch(): void
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['skills' => ['php', 'js']]),
            new Document('2', ['skills' => ['php']]),
            new Document('3', ['skills' => ['java']]),
        ]);

        $index->refresh();

        // minimum_should_match = 1: should match documents with at least 1 of the terms
        $query = new TermsSet('skills', ['php'], 1);
        $resultSet = $index->search($query);
        $this->assertEquals(2, $resultSet->count());

        // minimum_should_match = 1: should match documents with at least 1 of the terms
        $query = new TermsSet('skills', ['php', 'java'], 1);
        $resultSet = $index->search($query);
        $this->assertEquals(3, $resultSet->count());

        // minimum_should_match = 2: should match documents with at least 2 of the terms
        $query = new TermsSet('skills', ['php', 'js'], 2);
        $resultSet = $index->search($query);
        $this->assertEquals(1, $resultSet->count());

        // minimum_should_match = 2: should match documents with at least 2 of the terms
        $query = new TermsSet('skills', ['php', 'java'], 2);
        $resultSet = $index->search($query);
        $this->assertEquals(0, $resultSet->count());
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
        $query = new TermsSet('some_numeric_field', ['9876'], new Script('1'));
        $resultSet = $index->search($query);
        $this->assertEquals(1, $resultSet->count());

        // int
        $query = new TermsSet('some_numeric_field', [9876], new Script('1'));
        $resultSet = $index->search($query);
        $this->assertEquals(1, $resultSet->count());

        // float
        $query = new TermsSet('some_numeric_field', [9876.0], new Script('1'));
        $resultSet = $index->search($query);
        $this->assertEquals(1, $resultSet->count());
    }
}
