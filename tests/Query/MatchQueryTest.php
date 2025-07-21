<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\MatchQuery;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class MatchQueryTest extends BaseTest
{
    #[Group('unit')]
    public function testToArray(): void
    {
        $field = 'test';
        $testQuery = 'Nicolas Ruflin';
        $operator = 'and';
        $analyzer = 'myanalyzer';
        $boost = 2.0;
        $minimumShouldMatch = 2;
        $fuzziness = 0.3;
        $fuzzyRewrite = 'constant_score_boolean';
        $prefixLength = 3;
        $maxExpansions = 12;

        $query = new MatchQuery();
        $query->setFieldQuery($field, $testQuery);
        $this->hideDeprecated();
        $this->showDeprecated();
        $query->setFieldOperator($field, $operator);
        $query->setFieldAnalyzer($field, $analyzer);
        $query->setFieldBoost($field, $boost);
        $query->setFieldMinimumShouldMatch($field, $minimumShouldMatch);
        $query->setFieldFuzziness($field, $fuzziness);
        $query->setFieldFuzzyRewrite($field, $fuzzyRewrite);
        $query->setFieldPrefixLength($field, $prefixLength);
        $query->setFieldMaxExpansions($field, $maxExpansions);

        $expectedArray = [
            'match' => [
                $field => [
                    'query' => $testQuery,
                    'operator' => $operator,
                    'analyzer' => $analyzer,
                    'boost' => $boost,
                    'minimum_should_match' => $minimumShouldMatch,
                    'fuzziness' => $fuzziness,
                    'fuzzy_rewrite' => $fuzzyRewrite,
                    'prefix_length' => $prefixLength,
                    'max_expansions' => $maxExpansions,
                ],
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    #[Group('functional')]
    public function testMatch(): void
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

        $field = 'name';
        $operator = 'or';

        $query = new MatchQuery();
        $query->setFieldQuery($field, 'Basel New');
        $query->setFieldOperator($field, $operator);

        $resultSet = $index->search($query);

        $this->assertEquals(4, $resultSet->count());
    }

    #[Group('functional')]
    public function testMatchSetFieldBoost(): void
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

        $field = 'name';
        $operator = 'or';

        $query = new MatchQuery();
        $query->setFieldQuery($field, 'Basel New');
        $query->setFieldOperator($field, $operator);
        $query->setFieldBoost($field, 1.2);

        $resultSet = $index->search($query);

        $this->assertEquals(4, $resultSet->count());
    }

    #[Group('functional')]
    public function testMatchZeroTerm(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create([], [
            'recreate' => true,
        ]);

        $index->addDocuments([
            new Document('1', ['name' => 'Basel-Stadt']),
            new Document('2', ['name' => 'New York']),
        ]);

        $index->refresh();

        $query = new MatchQuery();
        $query->setFieldQuery('name', '');
        $query->setFieldZeroTermsQuery('name', MatchQuery::ZERO_TERM_ALL);

        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());
    }

    #[Group('unit')]
    public function testMatchFuzzinessType(): void
    {
        $field = 'test';
        $query = new MatchQuery();

        $fuzziness = 'AUTO';
        $query->setFieldFuzziness($field, $fuzziness);

        $parameters = $query->getParam($field);
        $this->assertEquals($fuzziness, $parameters['fuzziness']);

        $fuzziness = 0.3;
        $query->setFieldFuzziness($field, $fuzziness);

        $parameters = $query->getParam($field);
        $this->assertEquals($fuzziness, $parameters['fuzziness']);
    }

    #[Group('unit')]
    public function testConstruct(): void
    {
        $match = new MatchQuery(null, 'values');
        $this->assertEquals(['match' => []], $match->toArray());

        $match = new MatchQuery('field', null);
        $this->assertEquals(['match' => []], $match->toArray());

        $match1 = new MatchQuery('field', 'values');
        $match2 = new MatchQuery();
        $match2->setField('field', 'values');
        $this->assertEquals($match1->toArray(), $match2->toArray());
    }
}
