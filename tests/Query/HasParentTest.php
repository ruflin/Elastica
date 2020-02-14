<?php

namespace Elastica\Test\Query;

use Elastica\Mapping;
use Elastica\Query\HasParent;
use Elastica\Query\MatchAll;
use Elastica\Search;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class HasParentTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $q = new MatchAll();

        $type = 'test';

        $query = new HasParent($q, $type);

        $expectedArray = [
            'has_parent' => [
                'query' => $q->toArray(),
                'parent_type' => $type,
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group unit
     */
    public function testSetScope(): void
    {
        $q = new MatchAll();

        $type = 'test';

        $scope = 'foo';

        $query = new HasParent($q, $type);
        $query->setScope($scope);

        $expectedArray = [
            'has_parent' => [
                'query' => $q->toArray(),
                'parent_type' => $type,
                '_scope' => $scope,
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group functional
     */
    public function testHasParent(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('testhasparentjoin');
        $index->create([], true);
        $mapping = new Mapping([
            'text' => ['type' => 'keyword'],
            'name' => ['type' => 'keyword'],
            'my_join_field' => [
                'type' => 'join',
                'relations' => [
                    'question' => 'answer',
                ],
            ],
        ]);

        $index->setMapping($mapping);
        $index->refresh();

        $doc1 = $index->createDocument(1, [
            'text' => 'this is the 1st question',
            'my_join_field' => [
                'name' => 'question',
            ],
        ]);
        $doc2 = $index->createDocument(2, [
            'text' => 'this is the 2nd question',
            'my_join_field' => [
                'name' => 'question',
            ],
        ]);
        $index->addDocuments([$doc1, $doc2]);

        $doc3 = $index->createDocument(3, [
            'text' => 'this is an answer, the 1st',
            'name' => 'rico',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 1,
            ],
        ]);
        $doc4 = $index->createDocument(4, [
            'text' => 'this is an answer, the 2nd',
            'name' => 'fede',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ]);
        $doc5 = $index->createDocument(5, [
            'text' => 'this is an answer, the 3rd',
            'name' => 'fede',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ]);

        $index->addDocuments([$doc3, $doc4, $doc5], ['routing' => 1]);
        $index->refresh();

        $parentQuery = new HasParent(new MatchAll(), 'question');
        $search = new Search($index->getClient());
        $results = $search->search($parentQuery);
        $this->assertEquals(3, $results->count());
    }
}
