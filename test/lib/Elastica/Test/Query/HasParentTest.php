<?php

namespace Elastica\Test\Query;

use Elastica\Query\HasParentQuery;
use Elastica\Query\MatchAllQuery;
use Elastica\Test\Base as BaseTest;

class HasParentTest extends BaseTest
{
    public function testToArray()
    {
        $q = new MatchAllQuery();

        $type = 'test';

        $query = new HasParentQuery($q, $type);

        $expectedArray = array(
            'has_parent' => array(
                'query' => $q->toArray(),
                'type' => $type
            )
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }

    public function testSetScope()
    {
        $q = new MatchAllQuery();

        $type = 'test';

        $scope = 'foo';

        $query = new HasParentQuery($q, $type);
        $query->setScope($scope);

        $expectedArray = array(
            'has_parent' => array(
                'query' => $q->toArray(),
                'type' => $type,
                '_scope' => $scope
            )
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }
}
