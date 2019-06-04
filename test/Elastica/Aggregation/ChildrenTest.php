<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Children;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Type\Mapping;

class ChildrenTest extends BaseAggregationTest
{
    protected function _getIndexForTest(): Index
    {
        $client = $this->_getClient();
        $index = $client->getIndex('testaggregationchildren');
        $index->create(['settings' => ['index' => ['number_of_shards' => 2, 'number_of_replicas' => 1]]], true);

        $type = $index->getType(\strtolower(
            'typechildren'.\uniqid()
        ));

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
        ], $type->getName());

        $doc2 = new Document(2, [
            'text' => 'this is the 2nd question',
            'my_join_field' => [
                'name' => 'question',
            ],
        ], $type->getName());

        $index->addDocuments([$doc1, $doc2]);

        $doc3 = new Document(3, [
            'text' => 'this is an top answer, the 1st',
            'name' => 'rico',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 1,
            ],
        ], $type->getName(), $index->getName());

        $doc4 = new Document(4, [
            'text' => 'this is an top answer, the 2nd',
            'name' => 'fede',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ], $type->getName(), $index->getName());

        $doc5 = new Document(5, [
            'text' => 'this is an answer, the 3rd',
            'name' => 'fede',
            'my_join_field' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ], $type->getName(), $index->getName());

        $this->_getClient()->addDocuments([$doc3, $doc4, $doc5], ['routing' => 1]);
        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testChildrenAggregation()
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

    /**
     * @group functional
     */
    public function testChildrenAggregationCount()
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
}
