<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Filter_HasParentTest extends PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        $q = new Elastica_Query_MatchAll();

        $type = 'test';

        $filter = new Elastica_Filter_HasParent($q, $type);

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
        $q = new Elastica_Query_MatchAll();

        $type = 'test';

        $scope = 'foo';

        $filter = new Elastica_Filter_HasParent($q, $type);
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
