<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Query;
use Elastica\Query\MatchQuery;
use Elastica\Query\Term;
use Elastica\Rescore\Query as QueryRescore;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class RescoreTest extends BaseTest
{
    #[Group('unit')]
    public function testToArray(): void
    {
        $query = new Query();
        $mainQuery = new MatchQuery();
        $mainQuery = $mainQuery->setFieldQuery('test1', 'foo');
        $secQuery = new Term();
        $secQuery = $secQuery->setTerm('test2', 'bar', 2);
        $queryRescore = new QueryRescore($secQuery);
        $query->setQuery($mainQuery);
        $query->setRescore($queryRescore);
        $data = $query->toArray();

        $expected = [
            'query' => [
                'match' => [
                    'test1' => [
                        'query' => 'foo',
                    ],
                ],
            ],
            'rescore' => [
                'query' => [
                    'rescore_query' => [
                        'term' => [
                            'test2' => [
                                'value' => 'bar',
                                'boost' => 2,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $data);
    }

    #[Group('unit')]
    public function testSetSize(): void
    {
        $query = new Query();
        $mainQuery = new MatchQuery();
        $mainQuery = $mainQuery->setFieldQuery('test1', 'foo');
        $secQuery = new Term();
        $secQuery = $secQuery->setTerm('test2', 'bar', 2);
        $queryRescore = new QueryRescore($secQuery);
        $queryRescore->setWindowSize(50);
        $query->setQuery($mainQuery);
        $query->setRescore($queryRescore);
        $data = $query->toArray();

        $expected = [
            'query' => [
                'match' => [
                    'test1' => [
                        'query' => 'foo',
                    ],
                ],
            ],
            'rescore' => [
                'window_size' => 50,
                'query' => [
                    'rescore_query' => [
                        'term' => [
                            'test2' => [
                                'value' => 'bar',
                                'boost' => 2,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $data);
    }

    #[Group('unit')]
    public function testSetWeights(): void
    {
        $query = new Query();
        $mainQuery = new MatchQuery();
        $mainQuery = $mainQuery->setFieldQuery('test1', 'foo');
        $secQuery = new Term();
        $secQuery = $secQuery->setTerm('test2', 'bar', 2);
        $queryRescore = new QueryRescore($secQuery);
        $queryRescore->setWindowSize(50);
        $queryRescore->setQueryWeight(.7);
        $queryRescore->setRescoreQueryWeight(1.2);
        $query->setQuery($mainQuery);
        $query->setRescore($queryRescore);
        $data = $query->toArray();

        $expected = [
            'query' => [
                'match' => [
                    'test1' => [
                        'query' => 'foo',
                    ],
                ],
            ],
            'rescore' => [
                'window_size' => 50,
                'query' => [
                    'rescore_query' => [
                        'term' => [
                            'test2' => [
                                'value' => 'bar',
                                'boost' => 2,
                            ],
                        ],
                    ],
                    'query_weight' => 0.7,
                    'rescore_query_weight' => 1.2,
                ],
            ],
        ];

        $this->assertEquals($expected, $data);
    }

    #[Group('functional')]
    public function testMultipleQueries(): void
    {
        $query = new Query();
        $mainQuery = new MatchQuery();
        $mainQuery = $mainQuery->setFieldQuery('test1', 'foo');

        $secQuery1 = new Term();
        $secQuery1 = $secQuery1->setTerm('test2', 'bar', 1);
        $rescoreQuery1 = new QueryRescore();
        $rescoreQuery1->setRescoreQuery($secQuery1);

        $secQuery2 = new Term();
        $secQuery2 = $secQuery2->setTerm('test2', 'tom', 2);
        $rescoreQuery2 = new QueryRescore();
        $rescoreQuery2->setRescoreQuery($secQuery2);

        $query->setQuery($mainQuery);
        $query->setRescore([$rescoreQuery1, $rescoreQuery2]);
        $data = $query->toArray();

        $expected = [
            'query' => [
                'match' => [
                    'test1' => [
                        'query' => 'foo',
                    ],
                ],
            ],
            'rescore' => [
                [
                    'query' => [
                        'rescore_query' => [
                            'term' => [
                                'test2' => [
                                    'value' => 'bar',
                                    'boost' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'query' => [
                        'rescore_query' => [
                            'term' => [
                                'test2' => [
                                    'value' => 'tom',
                                    'boost' => 2,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $data);

        $index = $this->_createIndex();
        $index->refresh();
        $index->forcemerge();

        $results = $index->search($query);
        $response = $results->getResponse();

        $this->assertEquals(true, $response->isOk());
        $this->assertEquals(0, $results->getTotalHits());
    }

    #[Group('functional')]
    public function testQuery(): void
    {
        $mainQuery = (new MatchQuery())
            ->setFieldQuery('test1', 'foo')
        ;
        $secQuery = (new Term())
            ->setTerm('test2', 'bar', 2)
        ;
        $queryRescore = (new QueryRescore($secQuery))
            ->setWindowSize(50)
            ->setQueryWeight(.7)
            ->setRescoreQueryWeight(1.2)
        ;
        $query = (new Query())
            ->setQuery($mainQuery)
            ->setRescore($queryRescore)
        ;

        $index = $this->_createIndex();
        $index->refresh();
        $index->forcemerge();

        $results = $index->search($query);
        $response = $results->getResponse();

        $this->assertEquals(true, $response->isOk());
        $this->assertEquals(0, $results->getTotalHits());
    }
}
