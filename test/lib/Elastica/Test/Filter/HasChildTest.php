<?php
namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\HasChild;
use Elastica\Query\MatchAll;
use Elastica\Test\DeprecatedClassBase as BaseTest;

class HasChildTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testDeprecated()
    {
        $reflection = new \ReflectionClass(new HasChild(new MatchAll(), 'test'));
        $this->assertFileDeprecated($reflection->getFileName(), 'Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html');
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $q = new MatchAll();

        $type = 'test';

        $filter = new HasChild($q, $type);

        $expectedArray = [
            'has_child' => [
                'query' => $q->toArray(),
                'type' => $type,
            ],
        ];

        $this->assertEquals($expectedArray, $filter->toArray());
    }

    /**
     * @group functional
     */
    public function testSetType()
    {
        $index = $this->prepareSearchData();

        $filter = new HasChild(new MatchAll(), 'type_name');
        $this->assertEquals('type_name', $filter->getParam('type'));

        $filter->setType('new_type_name');
        $this->assertEquals('new_type_name', $filter->getParam('type'));

        $type = $index->getType('foo');
        $filter = new HasChild(new MatchAll(), $type);
        $this->assertEquals('foo', $filter->getParam('type'));

        $type = $index->getType('bar');
        $filter->setType($type);
        $this->assertEquals('bar', $filter->getParam('type'));

        $returnValue = $filter->setType('last');
        $this->assertInstanceOf('Elastica\Filter\HasChild', $returnValue);
    }

    /**
     * @group unit
     */
    public function testSetMinimumChildrenCount()
    {
        $query = new MatchAll();
        $filter = new HasChild($query, 'test');

        $filter->setMinimumChildrenCount(2);
        $this->assertEquals(2, $filter->getParam('min_children'));

        $returnValue = $filter->setMinimumChildrenCount(2);
        $this->assertInstanceOf('Elastica\Filter\HasChild', $returnValue);
    }

    /**
     * @group unit
     */
    public function testSetMaximumChildrenCount()
    {
        $query = new MatchAll();
        $filter = new HasChild($query, 'test');

        $filter->setMaximumChildrenCount(10);
        $this->assertEquals(10, $filter->getParam('max_children'));

        $returnValue = $filter->setMaximumChildrenCount(10);
        $this->assertInstanceOf('Elastica\Filter\HasChild', $returnValue);
    }

    /**
     * @group unit
     */
    public function testFilterInsideHasChild()
    {
        $f = new \Elastica\Filter\MatchAll();

        $type = 'test';

        $filter = new HasChild($f, $type);

        $expectedArray = [
            'has_child' => [
                'filter' => $f->toArray(),
                'type' => $type,
            ],
        ];

        $this->assertEquals($expectedArray, $filter->toArray());
    }

    /**
     * @group functional
     */
    public function testFilterInsideHasChildSearch()
    {
        $index = $this->prepareSearchData();

        $f = new \Elastica\Filter\Term();
        $f->setTerm('user', 'child1');
        $filter = new HasChild($f, 'child');

        $searchQuery = new \Elastica\Query();
        $searchQuery->setPostFilter($filter);
        $searchResults = $index->search($searchQuery);

        $this->assertEquals(1, $searchResults->count());

        $result = $searchResults->current()->getData();
        $expected = ['id' => 'parent1', 'user' => 'parent1', 'email' => 'parent1@test.com'];

        $this->assertEquals($expected, $result);
    }

    /**
     * @group functional
     */
    public function testQueryInsideHasChildSearch()
    {
        $index = $this->prepareSearchData();

        $f = new \Elastica\Query\Term();
        $f->setTerm('user', 'child1');
        $filter = new HasChild($f, 'child');

        $searchQuery = new \Elastica\Query();
        $searchQuery->setPostFilter($filter);
        $searchResults = $index->search($searchQuery);

        $this->assertEquals(1, $searchResults->count());

        $result = $searchResults->current()->getData();
        $expected = ['id' => 'parent1', 'user' => 'parent1', 'email' => 'parent1@test.com'];

        $this->assertEquals($expected, $result);
    }

    /**
     * @group functional
     */
    public function testTypeInsideHasChildSearch()
    {
        $index = $this->prepareSearchData();

        $f = new \Elastica\Query\Match();
        $f->setField('alt.name', 'testname');
        $filter = new HasChild($f, 'child');

        $searchQuery = new \Elastica\Query();
        $searchQuery->setPostFilter($filter);
        $searchResults = $index->search($searchQuery);

        $this->assertEquals(1, $searchResults->count());

        $result = $searchResults->current()->getData();
        $expected = ['id' => 'parent2', 'user' => 'parent2', 'email' => 'parent2@test.com'];

        $this->assertEquals($expected, $result);
    }

    private function prepareSearchData()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('has_child_test');
        $index->create([], true);

        $parentType = $index->getType('parent');

        $childType = $index->getType('child');
        $childMapping = new \Elastica\Type\Mapping($childType);
        $childMapping->setParent('parent');
        $childMapping->send();

        $altType = $index->getType('alt');
        $altDoc = new Document('alt1', ['name' => 'altname']);
        $altType->addDocument($altDoc);

        $parent1 = new Document('parent1', ['id' => 'parent1', 'user' => 'parent1', 'email' => 'parent1@test.com']);
        $parentType->addDocument($parent1);
        $parent2 = new Document('parent2', ['id' => 'parent2', 'user' => 'parent2', 'email' => 'parent2@test.com']);
        $parentType->addDocument($parent2);

        $child1 = new Document('child1', ['id' => 'child1', 'user' => 'child1', 'email' => 'child1@test.com']);
        $child1->setParent('parent1');
        $childType->addDocument($child1);
        $child2 = new Document('child2', ['id' => 'child2', 'user' => 'child2', 'email' => 'child2@test.com']);
        $child2->setParent('parent2');
        $childType->addDocument($child2);
        $child3 = new Document('child3', ['id' => 'child3', 'user' => 'child3', 'email' => 'child3@test.com', 'alt' => [['name' => 'testname']]]);
        $child3->setParent('parent2');
        $childType->addDocument($child3);

        $index->refresh();

        return $index;
    }
}
