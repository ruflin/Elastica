<?php

namespace Elastica\Test\Filter;

use Elastica\Document;
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

    public function testFilterInsideHasParent()
    {
        $f = new \Elastica\Filter\MatchAll();

        $type = 'test';

        $filter = new HasParent($f, $type);

        $expectedArray = array(
            'has_parent' => array(
                'filter' => $f->toArray(),
                'type' => $type
            )
        );

        $this->assertEquals($expectedArray, $filter->toArray());

    }

    public function testFilterInsideHasParentSearch()
    {
        $index = $this->prepareSearchData();

        $f = new \Elastica\Filter\Term();
        $f->setTerm('user', 'parent1');
        $filter = new HasParent($f, 'parent');

        $searchQuery = new \Elastica\Query();
        $searchQuery->setFilter($filter);
        $searchResults = $index->search($searchQuery);

        $this->assertEquals(1, $searchResults->count());

        $result = $searchResults->current()->getData();
        $expected = array('id' => 'child1', 'user' => 'child1', 'email' => 'child1@test.com');

        $this->assertEquals($expected, $result);
    }

    public function testQueryInsideHasParentSearch()
    {
        $index = $this->prepareSearchData();

        $f = new \Elastica\Query\Term();
        $f->setTerm('user', 'parent1');
        $filter = new HasParent($f, 'parent');

        $searchQuery = new \Elastica\Query();
        $searchQuery->setFilter($filter);
        $searchResults = $index->search($searchQuery);

        $this->assertEquals(1, $searchResults->count());

        $result = $searchResults->current()->getData();
        $expected = array('id' => 'child1', 'user' => 'child1', 'email' => 'child1@test.com');

        $this->assertEquals($expected, $result);
    }

    private function prepareSearchData()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('has_parent_test');
        $index->create(array(), true);

        $parentType = $index->getType('parent');

        $childType = $index->getType('child');
        $childMapping = new \Elastica\Type\Mapping($childType);
        $childMapping->setParent('parent');
        $childMapping->send();

        $parent1 = new Document('parent1', array('id' => 'parent1', 'user' => 'parent1', 'email' => 'parent1@test.com'));
        $parentType->addDocument($parent1);
        $parent2 = new Document('parent2', array('id' => 'parent2', 'user' => 'parent2', 'email' => 'parent2@test.com'));
        $parentType->addDocument($parent2);

        $child1 = new Document('child1', array('id' => 'child1', 'user' => 'child1', 'email' => 'child1@test.com'));
        $child1->setParent('parent1');
        $childType->addDocument($child1);
        $child2 = new Document('child2', array('id' => 'child2', 'user' => 'child2', 'email' => 'child2@test.com'));
        $child2->setParent('parent2');
        $childType->addDocument($child2);

        $index->refresh();
        return $index;
    }
}
