<?php

namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\HasChild;
use Elastica\Query\MatchAll;
use Elastica\Test\Base as BaseTest;

class HasChildTest extends BaseTest
{
    public function testToArray()
    {
        $q = new MatchAll();

        $type = 'test';

        $filter = new HasChild($q, $type);

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
        $q = new MatchAll();

        $type = 'test';

        $scope = 'foo';

        $filter = new HasChild($q, $type);
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

    public function testFilterInsideHasChild()
    {
        $f = new \Elastica\Filter\MatchAll();

        $type = 'test';

        $filter = new HasChild($f, $type);

        $expectedArray = array(
            'has_child' => array(
                'filter' => $f->toArray(),
                'type' => $type
            )
        );

        $this->assertEquals($expectedArray, $filter->toArray());

    }

    public function testFilterInsideHasChildSearch()
    {
        $index = $this->prepareSearchData();

        $f = new \Elastica\Filter\Term();
        $f->setTerm('user', 'child1');
        $filter = new HasChild($f, 'child');

        $searchQuery = new \Elastica\Query();
        $searchQuery->setFilter($filter);
        $searchResults = $index->search($searchQuery);

        $this->assertEquals(1, $searchResults->count());

        $result = $searchResults->current()->getData();
        $expected = array('id' => 'parent1', 'user' => 'parent1', 'email' => 'parent1@test.com');

        $this->assertEquals($expected, $result);
    }

    public function testQueryInsideHasChildSearch()
    {
        $index = $this->prepareSearchData();

        $f = new \Elastica\Query\Term();
        $f->setTerm('user', 'child1');
        $filter = new HasChild($f, 'child');

        $searchQuery = new \Elastica\Query();
        $searchQuery->setFilter($filter);
        $searchResults = $index->search($searchQuery);

        $this->assertEquals(1, $searchResults->count());

        $result = $searchResults->current()->getData();
        $expected = array('id' => 'parent1', 'user' => 'parent1', 'email' => 'parent1@test.com');

        $this->assertEquals($expected, $result);
    }
    
    public function testTypeInsideHasChildSearch()
    {
        $index = $this->prepareSearchData();
        
        $f = new \Elastica\Query\Match();
        $f->setField('alt.name', 'testname');
        $filter = new HasChild($f, 'child');
        
        $searchQuery = new \Elastica\Query();
        $searchQuery->setFilter($filter);
        $searchResults = $index->search($searchQuery);
        
        $this->assertEquals(1, $searchResults->count());
        
        $result = $searchResults->current()->getData();
        $expected = array('id' => 'parent2', 'user' => 'parent2', 'email' => 'parent2@test.com');
        
        $this->assertEquals($expected, $result);
    }

    private function prepareSearchData()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('has_child_test');
        $index->create(array(), true);

        $parentType = $index->getType('parent');
        
        $childType = $index->getType('child');
        $childMapping = new \Elastica\Type\Mapping($childType);
        $childMapping->setParent('parent');
        $childMapping->send();

        $altType = $index->getType('alt');
        $altDoc = new Document('alt1', array('name' => 'altname'));
        $altType->addDocument($altDoc);
        
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
        $child3 = new Document('child3', array('id' => 'child3', 'user' => 'child3', 'email' => 'child3@test.com', 'alt' => array(array('name' => 'testname'))));
        $child3->setParent('parent2');
        $childType->addDocument($child3);

        $index->refresh();
        return $index;
    }
}
