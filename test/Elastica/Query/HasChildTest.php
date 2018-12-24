<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\HasChild;
use Elastica\Query\MatchAll;
use Elastica\Search;
use Elastica\Test\Base as BaseTest;
use Elastica\Type\Mapping;

class HasChildTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $q = new MatchAll();

        $type = 'test';

        $query = new HasChild($q, $type);

        $expectedArray = [
            'has_child' => [
                'query' => $q->toArray(),
                'type' => $type,
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group unit
     */
    public function testSetScope()
    {
        $q = new MatchAll();

        $type = 'test';

        $scope = 'foo';

        $query = new HasChild($q, $type);
        $query->setScope($scope);

        $expectedArray = [
            'has_child' => [
                'query' => $q->toArray(),
                'type' => $type,
                '_scope' => $scope,
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group functional
     */
    public function testHasChildren()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('testhaschild');
        $index->create([], true);
        $type = $index->getType('_doc');

        $mapping = new Mapping();
        $mapping->setType($type);

        $mapping = new Mapping($type, [
            'text' => ['type' => 'keyword'],
            'name' => ['type' => 'keyword'],
            'my_join_field' => [
                'type' => 'join',
                'relations' => [
                    'question' => 'answer',
                ],
            ],
        ]);

        $type->setMapping($mapping);
        $index->refresh();

        $doc1 = new Document(1, [
            'text' => 'this is the 1st question',
            'my_join_field' => [
                'name' => 'question',
            ],
        ], '_doc');

        $doc2 = new Document(2, [
            'text' => 'this is the 2nd question',
            'my_join_field' => [
                'name' => 'question',
            ],
        ], '_doc');

        $index->addDocuments([$doc1, $doc2]);

        $doc3 = new Document(3, [
            'text' => 'this is an answer, the 1st',
            'name' => 'rico',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 1,
            ],
        ], '_doc', 'testhaschild');

        $doc4 = new Document(4, [
            'text' => 'this is an answer, the 2nd',
            'name' => 'fede',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ], '_doc', 'testhaschild');

        $doc5 = new Document(5, [
            'text' => 'this is an answer, the 3rd',
            'name' => 'fede',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ], '_doc', 'testhaschild');

        $this->_getClient()->addDocuments([$doc3, $doc4, $doc5], ['routing' => 1]);
        $index->refresh();

        $parentQuery = new HasChild(new MatchAll(), 'answer');
        $search = new Search($index->getClient());
        $results = $search->search($parentQuery);
        $this->assertEquals(2, $results->count());
    }

    protected function _getTestIndex()
    {
        $index = $this->_createIndex('has_child_test');

        $parentType = $index->getType('parent');

        $childType = $index->getType('child');
        $childMapping = new Mapping($childType);
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
