<?php

namespace Elastica\Test\Query;

use Elastica\Filter\TermFilter;
use Elastica\Filter\IdsFilter;
use Elastica\Query\ConstantScoreQuery;
use Elastica\Test\Base as BaseTest;

class ConstantScoreTest extends BaseTest
{
    public function dataProviderSampleQueries()
    {
        return array(
            array(
                new TermFilter(array('foo', 'bar')),
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
        $query = new ConstantScoreQuery();
        $query->setFilter($filter);
        if (is_string($expected)) {
            $expected = json_decode($expected, true);
        }
        $this->assertEquals($expected, $query->toArray());
    }

    public function testToArray()
    {
        $query = new ConstantScoreQuery();

        $boost = 1.2;
        $filter = new IdsFilter();
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
        $filter = new IdsFilter();
        $filter->setIds(array(1));

        $query = new ConstantScoreQuery($filter);

        $expectedArray = array(
            'constant_score' => array(
                'filter' => $filter->toArray(),
            )
        );

        $this->assertEquals($expectedArray, $query->toArray());

    }

    public function testConstructEmpty()
    {
        $query = new ConstantScoreQuery();
        $expectedArray = array('constant_score' => array());

        $this->assertEquals($expectedArray, $query->toArray());
    }
}
