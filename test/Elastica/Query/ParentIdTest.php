<?php

namespace Elastica\Query;

use Elastica\Document;
use Elastica\QueryBuilder\DSL\Query;
use Elastica\Search;
use Elastica\Test\Base as BaseTest;
use Elastica\Type\Mapping;

class ParentIdTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $type = '_doc';

        $query = new ParentId($type, 1);

        $expectedArray = [
            'parent_id' => [
                'type' => '_doc',
                'id' => 1,
                'ignore_unmapped' => false,
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group functional
     */
    public function testParentId()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('testparentid');
        $index->create([], true);
        $type = $index->getType('_doc');

        $mapping = new Mapping();
        $mapping->setType($type);

        $mapping = new Mapping($type, [
            'firstname' => ['type' => 'text', 'store' => true],
            'lastname' => ['type' => 'text'],
            'my_join_field' => [
                'type' => 'join',
                'relations' => [
                    'question' => 'answer',
                ],
            ],
        ]);

        $type->setMapping($mapping);

        $expected = [
            '_doc' => [
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
            ],
        ];

        $this->assertEquals($expected, $mapping->toArray());
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
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 1,
            ],
        ], '_doc', 'testparentid');

        $doc4 = new Document(4, [
            'text' => 'this is an answer, the 2nd',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ], '_doc', 'testparentid');

        $doc5 = new Document(5, [
            'text' => 'this is an answer, the 3rd',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ], '_doc', 'testparentid');

        $this->_getClient()->addDocuments([$doc3, $doc4, $doc5], ['routing' => 1]);
        $index->refresh();

        $parentQuery = new ParentId('answer', 1, true);
        $search = new Search($index->getClient());
        $results = $search->search($parentQuery);
        $this->assertEquals(1, $results->count());

        $result = $results->current();
        $data = $result->getData();
        $this->assertEquals($data['text'], 'this is an answer, the 1st');

        $parentQuery = new ParentId('answer', 2, true);
        $search = new Search($index->getClient());
        $results = $search->search($parentQuery);
        $this->assertEquals(2, $results->count());
    }

    /**
     * @group unit
     */
    public function testQueryBuilderParentId()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('testparentid');
        $index->create([], true);
        $type = $index->getType('_doc');

        $mapping = new Mapping();
        $mapping->setType($type);

        $mapping = new Mapping($type, [
            'firstname' => ['type' => 'text', 'store' => true],
            'lastname' => ['type' => 'text'],
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
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 1,
            ],
        ], '_doc', 'testparentid');

        $doc4 = new Document(4, [
            'text' => 'this is an answer, the 2nd',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ], '_doc', 'testparentid');

        $doc5 = new Document(5, [
            'text' => 'this is an answer, the 3rd',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ], '_doc', 'testparentid');

        $this->_getClient()->addDocuments([$doc3, $doc4, $doc5], ['routing' => 1]);
        $index->refresh();

        /** @var var Query $queryDSL */
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
