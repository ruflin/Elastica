<?php

namespace Elastica\Test\Filter;

use Elastica\Query\QueryString;
use Elastica\Filter\QueryFilter;
use Elastica\Test\Base as BaseTest;

class QueryTest extends BaseTest
{
    public function testSimple()
    {
        $query = new QueryString('foo bar');
        $filter = new QueryFilter($query);

        $expected = array(
            'query' => array(
                'query_string' => array(
                    'query' => 'foo bar',
                )
            )
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testExtended()
    {
        $query = new QueryString('foo bar');
        $filter = new QueryFilter($query);
        $filter->setCached(true);

        $expected = array(
            'fquery' => array(
                'query' => array(
                    'query_string' => array(
                        'query' => 'foo bar',
                    ),
                ),
                '_cache' => true
            )
        );

        $this->assertEquals($expected, $filter->toArray());
    }
}
