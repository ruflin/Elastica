<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\HasChildFilter;
use Elastica\Query\MatchAllQuery;
use Elastica\Test\Base as BaseTest;

class HasChildTest extends BaseTest
{
    public function testToArray()
    {
        $q = new MatchAllQuery();

        $type = 'test';

        $filter = new HasChildFilter($q, $type);

        $expectedArray = array(
            'has_child' => array(
                'query' => $q->toArray(),
                'type' => $type
            )
        );

        $this->assertEquals($expectedArray, $filter->toArray());
    }

    public function testSetScope()
    {
        $q = new MatchAllQuery();

        $type = 'test';

        $scope = 'foo';

        $filter = new HasChildFilter($q, $type);
        $filter->setScope($scope);

        $expectedArray = array(
            'has_child' => array(
                'query' => $q->toArray(),
                'type' => $type,
                '_scope' => $scope
            )
        );

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
