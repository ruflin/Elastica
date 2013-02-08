<?php

namespace Elastica\Test\Query;

use Elastica\Filter\Term;
use Elastica\Filter\Ids;
use Elastica\Query\ConstantScore;
use Elastica\Test\Base as BaseTest;

class ConstantScoreTest extends BaseTest
{
    public function dataProviderSampleQueries()
    {
        return array(
            array(
                new Term(array('foo', 'bar')),
                array(
                    'constant_score' => array(
                        'filter' => array(
                            'term' => array(
                                'foo',
                                'bar',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                array(
                    'and' => array(
                        array(
                            'query' => array(
                                'query_string' => array(
                                    'query' => 'foo',
                                    'default_field' => 'something',
                                ),
                            ),
                        ),
                        array(
                            'query' => array(
                                'query_string' => array(
                                    'query' => 'bar',
                                    'default_field' => 'something',
                                ),
                            ),
                        ),
                    ),
                ),
                '{"constant_score":{"filter":{"and":[{"query":{"query_string":{"query":"foo","default_field":"something"}}},{"query":{"query_string":{"query":"bar","default_field":"something"}}}]}}}',
            ),
        );
    }
    /**
     * @dataProvider dataProviderSampleQueries
     */
    public function testSimple($filter, $expected)
    {
        $query = new ConstantScore();
        $query->setFilter($filter);
        if (is_string($expected)) {
            $expected = json_decode($expected, true);
        }
        $this->assertEquals($expected, $query->toArray());
    }

    public function testToArray()
    {
        $query = new ConstantScore();

        $boost = 1.2;
        $filter = new Ids();
        $filter->setIds(array(1));

        $query->setFilter($filter);
        $query->setBoost($boost);

        $expectedArray = array(
            'constant_score' => array(
                'filter' => $filter->toArray(),
                'boost' => $boost
            )
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }

    public function testConstruct()
    {
        $filter = new Ids();
        $filter->setIds(array(1));

        $query = new ConstantScore($filter);

        $expectedArray = array(
            'constant_score' => array(
                'filter' => $filter->toArray(),
            )
        );

        $this->assertEquals($expectedArray, $query->toArray());

    }

    public function testConstructEmpty()
    {
        $query = new ConstantScore();
        $expectedArray = array('constant_score' => array());

        $this->assertEquals($expectedArray, $query->toArray());
    }
}
