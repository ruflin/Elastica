<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_HasParentTest extends PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        $q = new Elastica_Query_MatchAll();

        $type = 'test';

        $query = new Elastica_Query_HasParent($q, $type);

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
        $q = new Elastica_Query_MatchAll();

        $type = 'test';

        $scope = 'foo';

        $query = new Elastica_Query_HasParent($q, $type);
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
