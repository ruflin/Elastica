<?php

namespace Elastica\Test\Query;

use Elastica\Query\Rescore;
use Elastica\Query\Term;
use Elastica\Query\Match;
use Elastica\Test\Base as BaseTest;

class RescoreTest extends BaseTest
{
    public function testToArray()
    {
        $query = new Rescore();
        $main_query = new Match();
        $main_query = $main_query->setFieldQuery('test1', 'foo');
        $rescore_query = new Term();
        $rescore_query = $rescore_query->setTerm('test2', 'bar', 2);
        $query->setQuery($main_query);
        $query->setRescoreQuery($rescore_query);

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
        $query = new Rescore();
        $main_query = new Match();
        $main_query = $main_query->setFieldQuery('test1', 'foo');
        $rescore_query = new Term();
        $rescore_query = $rescore_query->setTerm('test2', 'bar', 2);
        $query->setQuery($main_query);
        $query->setRescoreQuery($rescore_query);
        $query->setWindowSize(50);

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
        $query = new Rescore();
        $main_query = new Match();
        $main_query = $main_query->setFieldQuery('test1', 'foo');
        $rescore_query = new Term();
        $rescore_query = $rescore_query->setTerm('test2', 'bar', 2);
        $query->setQuery($main_query);
        $query->setRescoreQuery($rescore_query);
        $query->setWindowSize(50);
        $query->setQueryWeight(.7);
        $query->setRescoreQueryWeight(1.2);

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
                'query_weight' => 0.7,
                'rescore_query_weight' => 1.2
            ),
        );

        $this->assertEquals($expected, $data);
    }
}
