<?php

namespace Elastica\Test;

use Elastica\Rescore\QueryRescore;
use Elastica\Query\Term;
use Elastica\Query\Match;
use Elastica\Client;
use Elastica\Query;
use Elastica\Test\Base as BaseTest;

class QueryRescoreTest extends BaseTest
{
    public function testToArray()
    {
        $query = new QueryRescore();
        $rescoreQuery = new Term();
        $rescoreQuery = $rescoreQuery->setTerm('test2', 'bar', 2);
        $query->setQuery($rescoreQuery);

        $data = $query->toArray();
        $expected = array(
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
        $query = new QueryRescore();
        $rescoreQuery = new Term();
        $rescoreQuery = $rescoreQuery->setTerm('test2', 'bar', 2);
        $query->setQuery($rescoreQuery);
        $query->setWindowSize(50);

        $data = $query->toArray();
        $expected = array(
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
        $query = new QueryRescore();
        $rescoreQuery = new Term();
        $rescoreQuery = $rescoreQuery->setTerm('test2', 'bar', 2);
        $query->setQuery($rescoreQuery);
        $query->setWindowSize(50);
        $query->setQueryWeight(.7);
        $query->setRescoreQueryWeight(1.2);

        $data = $query->toArray();
        $expected = array(
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
                    'rescore_query_weight' => 1.2
                ),
            ),
        );

        $this->assertEquals($expected, $data);
    }
}
