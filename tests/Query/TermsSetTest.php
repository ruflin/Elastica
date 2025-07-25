<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Query\TermsSet;
use Elastica\Script\Script;
use Elastica\Test\Base as BaseTest;
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
    public function testEmptyField(): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('TermsSet field name has to be set');

        new TermsSet('', ['foo', 'bar'], 'match_field');
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
