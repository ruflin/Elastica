<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\AvgBucket;
use Elastica\Aggregation\Avg;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Query;

class AvgBucketTest extends BaseAggregationTest
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

        $avgBucketAggregation = new AvgBucket(
            'avg_likes_by_page',
            'pages > avg_likes'
        );

        $sumLikes = new Avg('avg_likes');
        $sumLikes->setField('likes');

        $groupByPage = new Terms('pages');
        $groupByPage
            ->setField('page')
            ->setSize(2)
            ->addAggregation($sumLikes);

        $query = Query::create([])->addAggregation($groupByPage)->addAggregation($avgBucketAggregation);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('avg_likes_by_page');

        $this->assertEquals(161.5, $results['value']);
    }

    /**
     * @group unit
     */
    public function testConstructThroughSetters()
    {
        $serialDiffAgg = new AvgBucket('avg_bucket');

        $serialDiffAgg
            ->setBucketsPath('pages > avg_likes_by_page')
            ->setFormat('test_format')
            ->setGapPolicy(10);

        $expected = [
            'avg_bucket' => [
                'buckets_path' => 'pages > avg_likes_by_page',
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
        $serialDiffAgg = new AvgBucket('avg_bucket');
        $serialDiffAgg->toArray();
    }
}
