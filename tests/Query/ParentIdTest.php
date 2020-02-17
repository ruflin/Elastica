<?php

namespace Elastica\Query;

use Elastica\Mapping;
use Elastica\QueryBuilder\DSL\Query;
use Elastica\Search;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class ParentIdTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $query = new ParentId('join', '1');
        $expectedArray = [
            'parent_id' => [
                'type' => 'join',
                'id' => 1,
                'ignore_unmapped' => false,
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group functional
     */
    public function testParentId(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('testparentid');
        $index->create([], true);

        $mapping = new Mapping([
            'firstname' => ['type' => 'text', 'store' => true],
            'lastname' => ['type' => 'text'],
            'my_join_field' => [
                'type' => 'join',
                'relations' => [
                    'question' => 'answer',
                ],
            ],
        ]);

        $index->setMapping($mapping);

        $expected = [
            'properties' => [
                'firstname' => ['type' => 'text', 'store' => true],
                'lastname' => ['type' => 'text'],
                'my_join_field' => [
                    'type' => 'join',
                    'relations' => [
                        'question' => 'answer',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $mapping->toArray());
        $index->refresh();

        $doc1 = $index->createDocument('1', [
            'text' => 'this is the 1st question',
            'my_join_field' => [
                'name' => 'question',
            ],
        ]);
        $doc2 = $index->createDocument('2', [
            'text' => 'this is the 2nd question',
            'my_join_field' => [
                'name' => 'question',
            ],
        ]);
        $index->addDocuments([$doc1, $doc2]);

        $doc3 = $index->createDocument('3', [
            'text' => 'this is an answer, the 1st',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 1,
            ],
        ]);
        $doc4 = $index->createDocument('4', [
            'text' => 'this is an answer, the 2nd',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => '2',
            ],
        ]);
        $doc5 = $index->createDocument('5', [
            'text' => 'this is an answer, the 3rd',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => '2',
            ],
        ]);
        $this->_getClient()->addDocuments([$doc3, $doc4, $doc5], ['routing' => 1]);
        $index->refresh();

        $parentQuery = new ParentId('answer', 1, true);
        $search = new Search($index->getClient());
        $results = $search->search($parentQuery);
        $this->assertEquals(1, $results->count());

        $result = $results->current();
        $data = $result->getData();
        $this->assertEquals($data['text'], 'this is an answer, the 1st');

        $parentQuery = new ParentId('answer', '2', true);
        $search = new Search($index->getClient());
        $results = $search->search($parentQuery);
        $this->assertEquals(2, $results->count());
    }

    /**
     * @group unit
     */
    public function testQueryBuilderParentId(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('testparentid');
        $index->create([], true);

        $mapping = new Mapping([
            'firstname' => ['type' => 'text', 'store' => true],
            'lastname' => ['type' => 'text'],
            'my_join_field' => [
                'type' => 'join',
                'relations' => [
                    'question' => 'answer',
                ],
            ],
        ]);

        $index->setMapping($mapping);
        $index->refresh();

        $doc1 = $index->createDocument('1', [
            'text' => 'this is the 1st question',
            'my_join_field' => [
                'name' => 'question',
            ],
        ]);

        $doc2 = $index->createDocument('2', [
            'text' => 'this is the 2nd question',
            'my_join_field' => [
                'name' => 'question',
            ],
        ]);
        $index->addDocuments([$doc1, $doc2]);

        $doc3 = $index->createDocument('3', [
            'text' => 'this is an answer, the 1st',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 1,
            ],
        ]);
        $doc4 = $index->createDocument('4', [
            'text' => 'this is an answer, the 2nd',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ]);
        $doc5 = $index->createDocument('5', [
            'text' => 'this is an answer, the 3rd',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ]);
        $this->_getClient()->addDocuments([$doc3, $doc4, $doc5], ['routing' => 1]);
        $index->refresh();

        $queryDSL = new Query();
        $parentId = $queryDSL->parent_id('answer', 1, true);
        $search = new Search($index->getClient());
        $results = $search->search($parentId);

        $this->assertEquals(1, $results->count());

        $result = $results->current();
        $data = $result->getData();
        $this->assertEquals($data['text'], 'this is an answer, the 1st');

        $parentId = $queryDSL->parent_id('answer', 2, true);
        $search = new Search($index->getClient());
        $results = $search->search($parentId);

        $this->assertEquals(2, $results->count());
    }
}
