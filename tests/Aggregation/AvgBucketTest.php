<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Avg;
use Elastica\Aggregation\AvgBucket;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;

class AvgBucketTest extends BaseAggregationTest
{
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

    /**
     * @group functional
     */
    public function testAvgBucketAggregation()
    {
        $query = Query::create([])
            ->addAggregation(
                (new Terms('pages'))
                    ->setField('page')
                    ->setSize(2)
                    ->addAggregation(
                        (new Avg('avg_likes'))
                            ->setField('likes')
                    )
            )
            ->addAggregation(
                (new AvgBucket('avg_likes_by_page'))
                    ->setBucketsPath('pages>avg_likes')
            );

        $results = $this->_getIndexForTest()->search($query)->getAggregations();

        $this->assertEquals(168, $results['pages']['buckets'][0]['avg_likes']['value']);
        $this->assertEquals(155, $results['pages']['buckets'][1]['avg_likes']['value']);
        $this->assertEquals(161.5, $results['avg_likes_by_page']['value']);
    }

    /**
     * @group unit
     */
    public function testConstructThroughSetters()
    {
        $serialDiffAgg = new AvgBucket('avg_bucket');

        $serialDiffAgg
            ->setBucketsPath('pages>avg_likes_by_page')
            ->setFormat('test_format')
            ->setGapPolicy(10);

        $expected = [
            'avg_bucket' => [
                'buckets_path' => 'pages>avg_likes_by_page',
                'format' => 'test_format',
                'gap_policy' => 10,
            ],
        ];

        $this->assertEquals($expected, $serialDiffAgg->toArray());
    }

    /**
     * @group unit
     */
    public function testToArrayInvalidBucketsPath()
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $serialDiffAgg = new AvgBucket('avg_bucket');
        $serialDiffAgg->toArray();
    }
}
