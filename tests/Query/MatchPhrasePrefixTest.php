<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\MatchPhrasePrefix;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class MatchPhrasePrefixTest extends BaseTest
{
    #[Group('unit')]
    public function testToArray(): void
    {
        $field = 'test';
        $testQuery = 'Nicolas Ruflin';
        $analyzer = 'myanalyzer';
        $boost = 2.0;
        $maxExpansions = 10;

        $query = new MatchPhrasePrefix();
        $query->setFieldQuery($field, $testQuery);
        $query->setFieldAnalyzer($field, $analyzer);
        $query->setFieldBoost($field, $boost);
        $query->setFieldMaxExpansions($field, $maxExpansions);

        $expectedArray = [
            'match_phrase_prefix' => [
                $field => [
                    'query' => $testQuery,
                    'analyzer' => $analyzer,
                    'boost' => $boost,
                    'max_expansions' => $maxExpansions,
                ],
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    #[Group('functional')]
    public function testMatchPhrasePrefix(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create([], [
            'recreate' => true,
        ]);

        $index->addDocuments([
            new Document('1', ['name' => 'Basel-Stadt']),
            new Document('2', ['name' => 'New York']),
            new Document('3', ['name' => 'New Hampshire']),
            new Document('4', ['name' => 'Basel Land']),
        ]);

        $index->refresh();

        $query = new MatchPhrasePrefix();
        $query->setFieldQuery('name', 'New');

        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());
    }
}
