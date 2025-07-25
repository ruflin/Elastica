<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Query\BoolQuery;
use Elastica\Query\Ids;
use Elastica\Query\Term;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class BoolQueryTest extends BaseTest
{
    #[Group('unit')]
    public function testToArray(): void
    {
        $query = new BoolQuery();

        $idsQuery1 = new Ids();
        $idsQuery1->setIds('1');

        $idsQuery2 = new Ids();
        $idsQuery2->setIds('2');

        $idsQuery3 = new Ids();
        $idsQuery3->setIds('3');

        $filter1 = new Term();
        $filter1->setTerm('test', '1');

        $filter2 = new Term();
        $filter2->setTerm('username', 'ruth');

        $boost = 1.2;
        $minMatch = 2;

        $query->setBoost($boost);
        $query->setMinimumShouldMatch($minMatch);
        $query->addMust($idsQuery1);
        $query->addMustNot($idsQuery2);
        $query->addShould($idsQuery3->toArray());
        $query->addFilter($filter1);
        $query->addFilter($filter2);

        $expectedArray = [
            'bool' => [
                'must' => [$idsQuery1->toArray()],
                'should' => [$idsQuery3->toArray()],
                'filter' => [$filter1->toArray(), $filter2->toArray()],
                'minimum_should_match' => $minMatch,
                'must_not' => [$idsQuery2->toArray()],
                'boost' => $boost,
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * Test to resolve the following issue.
     *
     * @see https://groups.google.com/forum/?fromgroups#!topic/elastica-php-client/zK_W_hClfvU
     */
    #[Group('unit')]
    public function testToArrayStructure(): void
    {
        $boolQuery = new BoolQuery();

        $term1 = new Term();
        $term1->setParam('interests', 84);

        $term2 = new Term();
        $term2->setParam('interests', 92);

        $boolQuery->addShould($term1)->addShould($term2);

        $jsonString = '{"bool":{"should":[{"term":{"interests":84}},{"term":{"interests":92}}]}}';
        $this->assertEquals($jsonString, \json_encode($boolQuery->toArray()));
    }

    #[Group('functional')]
    public function testSearch(): void
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test');
        $index->create([], [
            'recreate' => true,
        ]);

        $doc = new Document('1', ['id' => 1, 'email' => 'hans@test.com', 'username' => 'hans', 'test' => ['2', '4', '5']]);
        $index->addDocument($doc);
        $doc = new Document('2', ['id' => 2, 'email' => 'emil@test.com', 'username' => 'emil', 'test' => ['1', '3', '6']]);
        $index->addDocument($doc);
        $doc = new Document('3', ['id' => 3, 'email' => 'ruth@test.com', 'username' => 'ruth', 'test' => ['2', '3', '7']]);
        $index->addDocument($doc);
        $doc = new Document('4', ['id' => 4, 'email' => 'john@test.com', 'username' => 'john', 'test' => ['2', '4', '8']]);
        $index->addDocument($doc);

        // Refresh index
        $index->refresh();

        $boolQuery = new BoolQuery();
        $termQuery1 = new Term(['test' => '2']);
        $boolQuery->addMust($termQuery1);
        $resultSet = $index->search($boolQuery);

        $this->assertEquals(3, $resultSet->count());

        $termFilter = new Term(['test' => '4']);
        $boolQuery->addFilter($termFilter);
        $resultSet = $index->search($boolQuery);

        $this->assertEquals(2, $resultSet->count());

        $termQuery2 = new Term(['test' => '5']);
        $boolQuery->addMust($termQuery2);
        $resultSet = $index->search($boolQuery);

        $this->assertEquals(1, $resultSet->count());

        $termQuery3 = new Term(['username' => 'hans']);
        $boolQuery->addMust($termQuery3);
        $resultSet = $index->search($boolQuery);

        $this->assertEquals(1, $resultSet->count());

        $termQuery4 = new Term(['username' => 'emil']);
        $boolQuery->addMust($termQuery4);
        $resultSet = $index->search($boolQuery);

        $this->assertEquals(0, $resultSet->count());
    }

    #[Group('functional')]
    public function testEmptyBoolQuery(): void
    {
        $index = $this->_createIndex();

        $docNumber = 3;
        for ($i = 0; $i < $docNumber; ++$i) {
            $doc = new Document((string) $i, ['email' => 'test@test.com']);
            $index->addDocument($doc);
        }

        $index->refresh();

        $boolQuery = new BoolQuery();

        $resultSet = $index->search($boolQuery);

        $this->assertEquals($resultSet->count(), $docNumber);
    }
}
