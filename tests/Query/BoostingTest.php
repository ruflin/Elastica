<?php

namespace Elastica\Test\Query;

use Elastica\Query\Boosting;
use Elastica\Query\Term;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class BoostingTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $keyword = 'vital';
        $negativeKeyword = 'Mercury';

        $query = new Boosting();
        $positiveQuery = new Term(['name' => $keyword]);
        $negativeQuery = new Term(['name' => $negativeKeyword]);
        $query->setPositiveQuery($positiveQuery);
        $query->setNegativeQuery($negativeQuery);
        $query->setNegativeBoost(0.3);

        $expected = [
            'boosting' => [
                'positive' => $positiveQuery->toArray(),
                'negative' => $negativeQuery->toArray(),
                'negative_boost' => 0.3,
            ],
        ];
        $this->assertEquals($expected, $query->toArray());
    }

    /**
     * @group unit
     */
    public function testNegativeBoost(): void
    {
        $keyword = 'vital';
        $negativeKeyword = 'mercury';

        $query = new Boosting();
        $positiveQuery = new Term();
        $positiveQuery->setTerm('name', $keyword, 5.0);
        $negativeQuery = new Term();
        $negativeQuery->setTerm('name', $negativeKeyword, 8.0);
        $query->setPositiveQuery($positiveQuery);
        $query->setNegativeQuery($negativeQuery);
        $query->setNegativeBoost(23.0);
        $query->setParam('boost', 42.0);

        $queryToCheck = $query->toArray();
        $this->assertEquals($queryToCheck['boosting']['boost'], 42.0);
        $this->assertEquals($queryToCheck['boosting']['positive']['term']['name']['boost'], 5.0);
        $this->assertEquals($queryToCheck['boosting']['negative']['term']['name']['boost'], 8.0);
        $this->assertEquals($queryToCheck['boosting']['negative_boost'], 23.0);
    }
}
