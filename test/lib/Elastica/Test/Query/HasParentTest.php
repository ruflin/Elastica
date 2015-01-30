<?php

namespace Elastica\Test\Query;

use Elastica\Query\HasParent;
use Elastica\Query\MatchAll;
use Elastica\Test\Base as BaseTest;

class HasParentTest extends BaseTest
{
    public function testToArray()
    {
        $q = new MatchAll();

        $type = 'test';

        $query = new HasParent($q, $type);

        $expectedArray = array(
            'has_parent' => array(
                'query' => $q->toArray(),
                'type' => $type,
            ),
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }

    public function testSetScope()
    {
        $q = new MatchAll();

        $type = 'test';

        $scope = 'foo';

        $query = new HasParent($q, $type);
        $query->setScope($scope);

        $expectedArray = array(
            'has_parent' => array(
                'query' => $q->toArray(),
                'type' => $type,
                '_scope' => $scope,
            ),
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }
}
