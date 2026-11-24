<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Mapping;
use Elastica\Query\HasParent;
use Elastica\Query\InnerHits;
use Elastica\Query\MatchAll;
use Elastica\Search;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class HasParentTest extends BaseTest
{
    #[Group('unit')]
    public function testInnerHits(): void
    {
        $match = new MatchAll();
        $query = new HasParent($match, 'question');
        $innerHits = (new InnerHits())->setName('parents')->setSize(1)->setSource(['text']);

        $this->assertSame($query, $query->setInnerHits($innerHits));
        $this->assertEquals([
            'has_parent' => [
                'query' => $match->toArray(),
                'parent_type' => 'question',
                'inner_hits' => ['name' => 'parents', 'size' => 1, '_source' => ['text']],
            ],
        ], $query->toArray());
    }

    #[Group('unit')]
    public function testEmptyInnerHits(): void
    {
        $query = new HasParent(new MatchAll(), 'question');
        $query->setInnerHits(new InnerHits());

        $this->assertJsonStringEqualsJsonString(
            '{"has_parent":{"query":{"match_all":{}},"parent_type":"question","inner_hits":{}}}',
            \json_encode($query->toArray(), \JSON_THROW_ON_ERROR)
        );
    }

    #[Group('unit')]
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

    #[Group('unit')]
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

    #[Group('functional')]
    public function testHasParent(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('testhasparentjoin');
        $index->create([], [
            'recreate' => true,
        ]);
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
            'name' => 'rico',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 1,
            ],
        ]);
        $doc4 = $index->createDocument('4', [
            'text' => 'this is an answer, the 2nd',
            'name' => 'fede',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ]);
        $doc5 = $index->createDocument('5', [
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

    #[Group('functional')]
    public function testHasParentInnerHits(): void
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'text' => ['type' => 'keyword'],
            'my_join_field' => [
                'type' => 'join',
                'relations' => [
                    'question' => 'answer',
                ],
            ],
        ]));

        $index->addDocuments([
            $index->createDocument('1', [
                'text' => 'this is the 1st question',
                'my_join_field' => ['name' => 'question'],
            ]),
            $index->createDocument('2', [
                'text' => 'this is the 2nd question',
                'my_join_field' => ['name' => 'question'],
            ]),
        ]);
        $index->addDocuments([
            $index->createDocument('3', [
                'text' => 'this is an answer, the 1st',
                'my_join_field' => ['name' => 'answer', 'parent' => '1'],
            ]),
            $index->createDocument('4', [
                'text' => 'this is an answer, the 2nd',
                'my_join_field' => ['name' => 'answer', 'parent' => '2'],
            ]),
        ], ['routing' => '1']);
        $index->refresh();

        $parentQuery = new HasParent(new MatchAll(), 'question');
        $parentQuery->setInnerHits((new InnerHits())->setName('parents')->setSource(['text']));

        $resultSet = $index->search($parentQuery);

        $this->assertSame(2, $resultSet->count());
        foreach ($resultSet as $result) {
            $this->assertSame('answer', $result->getSource()['my_join_field']['name']);

            $hit = $result->getHit();
            $this->assertArrayHasKey('inner_hits', $hit);
            $this->assertArrayHasKey('parents', $hit['inner_hits']);

            $parents = $hit['inner_hits']['parents']['hits']['hits'];
            $this->assertCount(1, $parents);
            $this->assertSame($result->getSource()['my_join_field']['parent'], $parents[0]['_id']);
            $this->assertSame(['text' => $parents[0]['_source']['text']], $parents[0]['_source']);
        }
    }

    #[Group('functional')]
    public function testHasParentWithoutInnerHits(): void
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'my_join_field' => [
                'type' => 'join',
                'relations' => [
                    'question' => 'answer',
                ],
            ],
        ]));
        $index->addDocuments([
            $index->createDocument('1', ['my_join_field' => ['name' => 'question']]),
        ]);
        $index->addDocuments([
            $index->createDocument('2', ['my_join_field' => ['name' => 'answer', 'parent' => '1']]),
        ], ['routing' => '1']);
        $index->refresh();

        $hit = $index->search(new HasParent(new MatchAll(), 'question'))->current()->getHit();
        $this->assertArrayNotHasKey('inner_hits', $hit);

        $parentQuery = (new HasParent(new MatchAll(), 'question'))->setInnerHits(new InnerHits());
        $hit = $index->search($parentQuery)->current()->getHit();
        $this->assertCount(1, $hit['inner_hits']['question']['hits']['hits']);
    }
}
