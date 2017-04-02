<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Query\ConstantScore;
use Elastica\Query\Ids;
use Elastica\Query\MatchAll;
use Elastica\Test\Base as BaseTest;

class ConstantScoreTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $query = new ConstantScore();

        $boost = 1.2;
        $filter = new Ids();
        $filter->setIds([1]);
        $query->setFilter($filter);
        $query->setBoost($boost);

        $expectedArray = [
            'constant_score' => [
                'filter' => $filter->toArray(),
                'boost' => $boost,
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group unit
     */
    public function testConstruct()
    {
        $filter = new Ids();
        $filter->setIds([1]);

        $query = new ConstantScore($filter);

        $expectedArray = [
            'constant_score' => [
                'filter' => $filter->toArray(),
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group functional
     */
    public function testQuery()
    {
        $index = $this->_createIndex();

        $type = $index->getType('constant_score');
        $type->addDocuments([
            new Document(1, ['id' => 1, 'email' => 'hans@test.com', 'username' => 'hans']),
            new Document(2, ['id' => 2, 'email' => 'emil@test.com', 'username' => 'emil']),
            new Document(3, ['id' => 3, 'email' => 'ruth@test.com', 'username' => 'ruth']),
        ]);

        // Refresh index
        $index->refresh();

        $boost = 1.3;
        $query_match = new MatchAll();

        $query = new ConstantScore();
        $query->setQuery($query_match);
        $query->setBoost($boost);

        $expectedArray = [
            'constant_score' => [
                'query' => $query_match->toArray(),
                'boost' => $boost,
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
        $resultSet = $type->search($query);

        $results = $resultSet->getResults();

        $this->assertEquals($resultSet->count(), 3);
        $this->assertEquals($results[1]->getScore(), 1.3);
    }

    /**
     * @group unit
     */
    public function testConstructEmpty()
    {
        $query = new ConstantScore();
        $expectedArray = ['constant_score' => []];

        $this->assertEquals($expectedArray, $query->toArray());
    }
}
