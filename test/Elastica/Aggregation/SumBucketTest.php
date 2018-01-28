<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\SumBucket;
use Elastica\Aggregation\Sum;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Query;

class SumBucketTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        $index->getType('test')->addDocuments([
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
        $this->_checkScriptInlineSetting();

        $sumBucketAggregation = new SumBucket(
            'sum_likes_by_page',
            'pages > sum_likes'
        );

        $sumLikes = new Sum('sum_likes');
        $sumLikes->setField('likes');

        $groupByPage = new Terms('pages');
        $groupByPage
            ->setField('page')
            ->setSize(2)
            ->addAggregation($sumLikes);

        $query = Query::create([])->addAggregation($groupByPage)->addAggregation($sumBucketAggregation);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('sum_likes_by_page');

        $this->assertEquals(491, $results['value']);
    }

    /**
     * @group unit
     */
    public function testConstructThroughSetters()
    {
        $serialDiffAgg = new SumBucket('sum_bucket');

        $serialDiffAgg
            ->setBucketsPath('pages > sum_likes_by_page')
            ->setFormat('test_format')
            ->setGapPolicy(10);

        $expected = [
            'sum_bucket' => [
                'buckets_path' => 'pages > sum_likes_by_page',
                'format' => 'test_format',
                'gap_policy' => 10,
            ],
        ];

        $this->assertEquals($expected, $serialDiffAgg->toArray());
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testToArrayInvalidBucketsPath()
    {
        $serialDiffAgg = new SumBucket('sum_bucket');
        $serialDiffAgg->toArray();
    }
}
