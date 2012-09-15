<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_ConstantScoreTest extends PHPUnit_Framework_TestCase
{
    public function dataProviderSampleQueries()
    {
        return array(
            array(
                new Elastica_Filter_Term (array('foo', 'bar')),
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
        $query = new Elastica_Query_ConstantScore();
        $query->setFilter($filter);
        if (is_string($expected)) {
            $expected = json_decode($expected, true);
        }
        $this->assertEquals($expected, $query->toArray());
    }

    public function testToArray()
    {
        $query = new Elastica_Query_ConstantScore();

        $boost = 1.2;
        $filter = new Elastica_Filter_Ids();
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
        $filter = new Elastica_Filter_Ids();
        $filter->setIds(array(1));

        $query = new Elastica_Query_ConstantScore($filter);

        $expectedArray = array(
            'constant_score' => array(
                'filter' => $filter->toArray(),
            )
        );

        $this->assertEquals($expectedArray, $query->toArray());

    }

    public function testConstructEmpty()
    {
        $query = new Elastica_Query_ConstantScore();
        $expectedArray = array('constant_score' => array());

        $this->assertEquals($expectedArray, $query->toArray());
    }
}
