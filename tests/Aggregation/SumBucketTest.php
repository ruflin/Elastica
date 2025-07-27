<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\GapPolicyInterface;
use Elastica\Aggregation\Sum;
use Elastica\Aggregation\SumBucket;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class SumBucketTest extends BaseAggregationTestCase
{
    #[Group('functional')]
    public function testSumBucketAggregation(): void
    {
        $query = Query::create([])
            ->addAggregation(
                (new Terms('pages'))
                    ->setField('page')
                    ->setSize(2)
                    ->addAggregation(
                        (new Sum('sum_likes'))
                            ->setField('likes')
                    )
            )
            ->addAggregation(
                new SumBucket('sum_likes_by_page', 'pages>sum_likes')
            )
        ;

        $results = $this->getIndexForTest()->search($query)->getAggregations();

        $this->assertEquals(336, $results['pages']['buckets'][0]['sum_likes']['value']);
        $this->assertEquals(155, $results['pages']['buckets'][1]['sum_likes']['value']);
        $this->assertEquals(491, $results['sum_likes_by_page']['value']);
    }

    #[Group('unit')]
    public function testConstructThroughSetters(): void
    {
        $aggregation = (new SumBucket('sum_bucket', 'pages>sum_likes_by_page'))
            ->setFormat('test_format')
            ->setGapPolicy(GapPolicyInterface::INSERT_ZEROS)
        ;

        $expected = [
            'sum_bucket' => [
                'buckets_path' => 'pages>sum_likes_by_page',
                'format' => 'test_format',
                'gap_policy' => GapPolicyInterface::INSERT_ZEROS,
            ],
        ];

        $this->assertEquals($expected, $aggregation->toArray());
    }

    private function getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            Document::create(['page' => 1, 'likes' => 180]),
            Document::create(['page' => 1, 'likes' => 156]),
            Document::create(['page' => 2, 'likes' => 155]),
        ], ['refresh' => true]);

        return $index;
    }
}
