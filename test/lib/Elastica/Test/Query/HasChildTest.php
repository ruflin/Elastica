<?php

namespace Elastica\Test\Query;

use Elastica\Query\HasChild;
use Elastica\Query\MatchAll;
use Elastica\Test\Base as BaseTest;

class HasChildTest extends BaseTest
{
    public function testToArray()
    {
        $q = new MatchAll();

        $type = 'test';

        $query = new HasChild($q, $type);

        $expectedArray = array(
            'has_child' => array(
                'query' => $q->toArray(),
                'type' => $type
            )
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }

    public function testSetScope()
    {
        $q = new MatchAll();

        $type = 'test';

        $scope = 'foo';

        $query = new HasChild($q, $type);
        $query->setScope($scope);

        $expectedArray = array(
            'has_child' => array(
                'query' => $q->toArray(),
                'type' => $type,
                '_scope' => $scope
            )
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }
}
