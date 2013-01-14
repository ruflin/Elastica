<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\HasParentFilter;
use Elastica\Query\MatchAllQuery;
use Elastica\Test\Base as BaseTest;

class HasParentTest extends BaseTest
{
    public function testToArray()
    {
        $q = new MatchAllQuery();

        $type = 'test';

        $filter = new HasParentFilter($q, $type);

        $expectedArray = array(
            'has_parent' => array(
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

        $filter = new HasParentFilter($q, $type);
        $filter->setScope($scope);

        $expectedArray = array(
            'has_parent' => array(
                'query' => $q->toArray(),
                'type' => $type,
                '_scope' => $scope
            )
        );

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
