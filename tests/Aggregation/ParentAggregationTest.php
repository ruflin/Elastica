<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\ParentAggregation;
use Elastica\Aggregation\Terms;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class ParentAggregationTest extends BaseAggregationTestCase
{
    #[Group('functional')]
    public function testParentAggregation(): void
    {
        $agg = new ParentAggregation('question');
        $agg->setType('answer');

        $tags = new Terms('tags');
        $tags->setField('tags');

        $agg->addAggregation($tags);

        $query = new Query();
        $query->addAggregation($agg);

        $index = $this->_getIndexForTest();
        $aggregations = $index->search($query)->getAggregations();

        // check parent aggregation exists
        $this->assertArrayHasKey('question', $aggregations);

        $parentAggregations = $aggregations['question'];

        // check tags aggregation exists inside parent aggregation
        $this->assertArrayHasKey('tags', $parentAggregations);
    }

    #[Group('functional')]
    public function testParentAggregationCount(): void
    {
        $topNames = new Terms('top-names');
        $topNames->setField('owner')
            ->setSize(10)
        ;

        $toQuestions = new ParentAggregation('to-questions');
        $toQuestions->setType('answer');

        $topTags = new Terms('top-tags');
        $topTags->setField('tags')
            ->setSize(10)
        ;

        $toQuestions->addAggregation($topTags);
        $topNames->addAggregation($toQuestions);

        $query = new Query();
        $query->addAggregation($topNames);

        $index = $this->_getIndexForTest();
        $aggregations = $index->search($query)->getAggregations();

        $topNamesAggregation = $aggregations['top-names'];
        $this->assertCount(2, $topNamesAggregation['buckets']);
        $this->assertEquals(2, $topNamesAggregation['buckets'][0]['to-questions']['doc_count']);
        $this->assertEquals(1, $topNamesAggregation['buckets'][1]['to-questions']['doc_count']);

        $samTags = [
            ['key' => 'file-transfer', 'doc_count' => 2],
            ['key' => 'windows-server-2008', 'doc_count' => 2],
            ['key' => 'windows-server-2003', 'doc_count' => 1],
        ];
        $this->assertEquals($samTags, $topNamesAggregation['buckets'][0]['to-questions']['top-tags']['buckets']);

        $samTags = [
            ['key' => 'file-transfer', 'doc_count' => 1],
            ['key' => 'windows-server-2008', 'doc_count' => 1],
        ];
        $this->assertEquals($samTags, $topNamesAggregation['buckets'][1]['to-questions']['top-tags']['buckets']);
    }

    protected function _getIndexForTest(): Index
    {
        $client = $this->_getClient();
        $index = $client->getIndex('testaggregationparent');
        $index->create(['settings' => ['index' => ['number_of_shards' => 2, 'number_of_replicas' => 1]]]);

        $mapping = new Mapping([
            'text' => ['type' => 'keyword'],
            'tags' => ['type' => 'keyword'],
            'owner' => ['type' => 'keyword'],
            'join' => [
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
            'tags' => [
                'windows-server-2003',
                'windows-server-2008',
                'file-transfer',
            ],
            'join' => [
                'name' => 'question',
            ],
        ]);

        $doc2 = $index->createDocument('2', [
            'text' => 'this is the 2nd question',
            'tags' => [
                'windows-server-2008',
                'file-transfer',
            ],
            'join' => [
                'name' => 'question',
            ],
        ]);

        $index->addDocuments([$doc1, $doc2]);

        $doc3 = $index->createDocument('3', [
            'text' => 'this is an top answer, the 1st',
            'owner' => 'Sam',
            'join' => [
                'name' => 'answer',
                'parent' => 1,
            ],
        ]);

        $doc4 = $index->createDocument('4', [
            'text' => 'this is a top answer, the 2nd',
            'owner' => 'Sam',
            'join' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ]);

        $doc5 = $index->createDocument('5', [
            'text' => 'this is an answer, the 3rd',
            'owner' => 'Troll',
            'join' => [
                'name' => 'answer',
                'parent' => 2,
            ],
        ]);

        $this->_getClient()->addDocuments([$doc3], ['routing' => 1]);
        $this->_getClient()->addDocuments([$doc4, $doc5], ['routing' => 2]);
        $index->refresh();

        return $index;
    }
}
