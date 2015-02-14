<?php

namespace Elastica\Test\Query;

use Elastica\Query;
use Elastica\Query\Match;
use Elastica\Query\Term;
use Elastica\Rescore\Query as QueryRescore;
use Elastica\Test\Base as BaseTest;

class RescoreTest extends BaseTest
{
    /**
     * @var Index
     */
    protected $_index;

    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex();
        $this->_index->refresh();
    }

    public function testToArray()
    {
        $query = new Query();
        $mainQuery = new Match();
        $mainQuery = $mainQuery->setFieldQuery('test1', 'foo');
        $secQuery = new Term();
        $secQuery = $secQuery->setTerm('test2', 'bar', 2);
        $queryRescore = new QueryRescore($secQuery);
        $query->setQuery($mainQuery);
        $query->setRescore($queryRescore);
        $data = $query->toArray();

        $expected = array(
            'query' => array(
                'match' => array(
                    'test1' => array(
                        'query' => 'foo',
                    ),
                ),
            ),
            'rescore' => array(
                'query' => array(
                    'rescore_query' => array(
                        'term' => array(
                            'test2' => array(
                                'value' => 'bar',
                                'boost' => 2,
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $data);
    }

    public function testSetSize()
    {
        $query = new Query();
        $mainQuery = new Match();
        $mainQuery = $mainQuery->setFieldQuery('test1', 'foo');
        $secQuery = new Term();
        $secQuery = $secQuery->setTerm('test2', 'bar', 2);
        $queryRescore = new QueryRescore($secQuery);
        $queryRescore->setWindowSize(50);
        $query->setQuery($mainQuery);
        $query->setRescore($queryRescore);
        $data = $query->toArray();

        $expected = array(
            'query' => array(
                'match' => array(
                    'test1' => array(
                        'query' => 'foo',
                    ),
                ),
            ),
            'rescore' => array(
                'window_size' => 50,
                'query' => array(
                    'rescore_query' => array(
                        'term' => array(
                            'test2' => array(
                                'value' => 'bar',
                                'boost' => 2,
                            ),
                        ),
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $data);
    }

    public function testSetWeights()
    {
        $query = new Query();
        $mainQuery = new Match();
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

        $expected = array(
            'query' => array(
                'match' => array(
                    'test1' => array(
                        'query' => 'foo',
                    ),
                ),
            ),
            'rescore' => array(
                'window_size' => 50,
                'query' => array(
                    'rescore_query' => array(
                        'term' => array(
                            'test2' => array(
                                'value' => 'bar',
                                'boost' => 2,
                            ),
                        ),
                    ),
                    'query_weight' => 0.7,
                    'rescore_query_weight' => 1.2,
                ),
            ),
        );

        $this->assertEquals($expected, $data);
    }

    public function testQuery()
    {
        $query = new Query();
        $mainQuery = new Match();
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

        $results = $this->_index->search($query);
        $response = $results->getResponse();

        $this->assertEquals(true, $response->isOk());
        $this->assertEquals(0, $results->getTotalHits());
    }
}
