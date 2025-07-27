<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Children;
use Elastica\Aggregation\Terms;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class ChildrenTest extends BaseAggregationTestCase
{
    #[Group('functional')]
    public function testChildrenAggregation(): void
    {
        $agg = new Children('answer');
        $agg->setType('answer');

        $names = new Terms('name');
        $names->setField('name');

        $agg->addAggregation($names);

        $query = new Query();
        $query->addAggregation($agg);

        $index = $this->_getIndexForTest();
        $aggregations = $index->search($query)->getAggregations();

        // check children aggregation exists
        $this->assertArrayHasKey('answer', $aggregations);

        $childrenAggregations = $aggregations['answer'];

        // check names aggregation exists inside children aggregation
        $this->assertArrayHasKey('name', $childrenAggregations);
    }

    #[Group('functional')]
    public function testChildrenAggregationCount(): void
    {
        $agg = new Children('answer');
        $agg->setType('answer');

        $names = new Terms('name');
        $names->setField('name');

        $agg->addAggregation($names);

        $query = new Query();
        $query->addAggregation($agg);

        $index = $this->_getIndexForTest();
        $aggregations = $index->search($query)->getAggregations();

        $childrenAggregations = $aggregations['answer'];
        $this->assertCount(2, $childrenAggregations['name']['buckets']);

        // check names aggregation works inside children aggregation
        $names = [
            ['key' => 'fede', 'doc_count' => 2],
            ['key' => 'rico', 'doc_count' => 1],
        ];
        $this->assertEquals($names, $childrenAggregations['name']['buckets']);
    }

    protected function _getIndexForTest(): Index
    {
        $client = $this->_getClient();
        $index = $client->getIndex('testaggregationchildren');
        $index->create(
            [
                'settings' => [
                    'index' => [
                        'number_of_shards' => 2,
                        'number_of_replicas' => 1,
                    ],
                ],
            ],
            [
                'recreate' => true,
            ]
        );

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
            'text' => 'this is an top answer, the 1st',
            'name' => 'rico',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 1,
            ],
        ]);

        $doc4 = $index->createDocument('4', [
            'text' => 'this is an top answer, the 2nd',
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

        $this->_getClient()->addDocuments([$doc3, $doc4, $doc5], ['routing' => 1]);
        $index->refresh();

        return $index;
    }
}
