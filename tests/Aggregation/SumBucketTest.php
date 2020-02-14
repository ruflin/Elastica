<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Sum;
use Elastica\Aggregation\SumBucket;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;

/**
 * @internal
 */
class SumBucketTest extends BaseAggregationTest
{
    /**
     * @group functional
     */
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
                (new SumBucket('sum_likes_by_page'))
                    ->setBucketsPath('pages>sum_likes')
            )
        ;

        $results = $this->_getIndexForTest()->search($query)->getAggregations();

        $this->assertEquals(336, $results['pages']['buckets'][0]['sum_likes']['value']);
        $this->assertEquals(155, $results['pages']['buckets'][1]['sum_likes']['value']);
        $this->assertEquals(491, $results['sum_likes_by_page']['value']);
    }

    /**
     * @group unit
     */
    public function testConstructThroughSetters(): void
    {
        $serialDiffAgg = new SumBucket('sum_bucket');

        $serialDiffAgg
            ->setBucketsPath('pages>sum_likes_by_page')
            ->setFormat('test_format')
            ->setGapPolicy(10)
        ;

        $expected = [
            'sum_bucket' => [
                'buckets_path' => 'pages>sum_likes_by_page',
                'format' => 'test_format',
                'gap_policy' => 10,
            ],
        ];

        $this->assertEquals($expected, $serialDiffAgg->toArray());
    }

    /**
     * @group unit
     */
    public function testToArrayInvalidBucketsPath(): void
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $serialDiffAgg = new SumBucket('sum_bucket');
        $serialDiffAgg->toArray();
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            Document::create(['page' => 1, 'likes' => 180]),
            Document::create(['page' => 1, 'likes' => 156]),
            Document::create(['page' => 2, 'likes' => 155]),
        ]);

        $index->refresh();

        return $index;
    }
}
