<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\Match;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class MatchTest extends BaseTest
{
    /**
     * @group unit
     */
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

        $query = new Match();
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

    /**
     * @group functional
     */
    public function testMatch(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create([], true);

        $index->addDocuments([
            new Document(1, ['name' => 'Basel-Stadt']),
            new Document(2, ['name' => 'New York']),
            new Document(3, ['name' => 'New Hampshire']),
            new Document(4, ['name' => 'Basel Land']),
        ]);

        $index->refresh();

        $field = 'name';
        $operator = 'or';

        $query = new Match();
        $query->setFieldQuery($field, 'Basel New');
        $query->setFieldOperator($field, $operator);

        $resultSet = $index->search($query);

        $this->assertEquals(4, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testMatchSetFieldBoost(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create([], true);

        $index->addDocuments([
            new Document(1, ['name' => 'Basel-Stadt']),
            new Document(2, ['name' => 'New York']),
            new Document(3, ['name' => 'New Hampshire']),
            new Document(4, ['name' => 'Basel Land']),
        ]);

        $index->refresh();

        $field = 'name';
        $operator = 'or';

        $query = new Match();
        $query->setFieldQuery($field, 'Basel New');
        $query->setFieldOperator($field, $operator);
        $query->setFieldBoost($field, 1.2);

        $resultSet = $index->search($query);

        $this->assertEquals(4, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testMatchSetFieldBoostWithString(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create([], true);

        $index->addDocuments([
            new Document(1, ['name' => 'Basel-Stadt']),
            new Document(2, ['name' => 'New York']),
            new Document(3, ['name' => 'New Hampshire']),
            new Document(4, ['name' => 'Basel Land']),
        ]);

        $index->refresh();

        $field = 'name';
        $operator = 'or';

        $query = new Match();
        $query->setFieldQuery($field, 'Basel New');
        $query->setFieldOperator($field, $operator);
        $query->setFieldBoost($field, '1.2');

        $resultSet = $index->search($query);

        $this->assertEquals(4, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testMatchZeroTerm(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create([], true);

        $index->addDocuments([
            new Document(1, ['name' => 'Basel-Stadt']),
            new Document(2, ['name' => 'New York']),
        ]);

        $index->refresh();

        $query = new Match();
        $query->setFieldQuery('name', '');
        $query->setFieldZeroTermsQuery('name', Match::ZERO_TERM_ALL);

        $resultSet = $index->search($query);

        $this->assertEquals(2, $resultSet->count());
    }

    /**
     * @group unit
     */
    public function testMatchFuzzinessType(): void
    {
        $field = 'test';
        $query = new Match();

        $fuzziness = 'AUTO';
        $query->setFieldFuzziness($field, $fuzziness);

        $parameters = $query->getParam($field);
        $this->assertEquals($fuzziness, $parameters['fuzziness']);

        $fuzziness = 0.3;
        $query->setFieldFuzziness($field, $fuzziness);

        $parameters = $query->getParam($field);
        $this->assertEquals($fuzziness, $parameters['fuzziness']);
    }

    /**
     * @group unit
     */
    public function testConstruct(): void
    {
        $match = new Match(null, 'values');
        $this->assertEquals(['match' => []], $match->toArray());

        $match = new Match('field', null);
        $this->assertEquals(['match' => []], $match->toArray());

        $match1 = new Match('field', 'values');
        $match2 = new Match();
        $match2->setField('field', 'values');
        $this->assertEquals($match1->toArray(), $match2->toArray());
    }
}
