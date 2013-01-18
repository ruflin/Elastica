<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\HasParent;
use Elastica\Query\MatchAll;
use Elastica\Test\Base as BaseTest;

class HasParentTest extends BaseTest
{
    public function testToArray()
    {
        $q = new MatchAll();

        $type = 'test';

        $filter = new HasParent($q, $type);

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
        $q = new MatchAll();

        $type = 'test';

        $scope = 'foo';

        $filter = new HasParent($q, $type);
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
