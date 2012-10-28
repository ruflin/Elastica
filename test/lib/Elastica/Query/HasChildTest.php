<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_HasChildTest extends PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        $q = new Elastica_Query_MatchAll();

        $type = 'test';

        $query = new Elastica_Query_HasChild($q, $type);

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
        $q = new Elastica_Query_MatchAll();

        $type = 'test';

        $scope = 'foo';

        $query = new Elastica_Query_HasChild($q, $type);
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
