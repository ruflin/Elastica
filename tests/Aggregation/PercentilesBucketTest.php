<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Avg;
use Elastica\Aggregation\PercentilesBucket;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Index;
use Elastica\Query;

/**
 * @internal
 */
class PercentilesBucketTest extends BaseAggregationTest
{
    /**
     * @group functional
     */
    public function testPercentilesBucketAggregation(): void
    {
        $query = Query::create([])
            ->addAggregation(
                (new Terms('pages'))
                    ->setField('page')
                    ->setSize(3)
                    ->addAggregation(
                        (new Avg('avg_likes'))
                            ->setField('likes')
                    )
            )
            ->addAggregation(
                (new PercentilesBucket('percentiles_likes_by_page'))
                    ->setBucketsPath('pages>avg_likes')
                    ->setPercents([5, 50, 95])
            )
        ;

        $results = $this->_getIndexForTest()->search($query)->getAggregations();

        $this->assertEquals(5, $results['pages']['buckets'][0]['avg_likes']['value']);
        $this->assertEquals(100, $results['pages']['buckets'][1]['avg_likes']['value']);
        $this->assertEquals(150, $results['pages']['buckets'][2]['avg_likes']['value']);
        $this->assertEquals(
            ['5.0' => 5, '50.0' => 100, '95.0' => 150],
            $results['percentiles_likes_by_page']['values']
        );
    }

    /**
     * @group unit
     */
    public function testConstructThroughSetters(): void
    {
        $serialDiffAgg = new PercentilesBucket('percentiles_bucket');

        $serialDiffAgg
            ->setBucketsPath('pages>avg_likes_by_page')
            ->setFormat('test_format')
            ->setPercents([10, 80])
            ->setGapPolicy(10)
            ->setKeyed(false)
        ;

        $expected = [
            'percentiles_bucket' => [
                'buckets_path' => 'pages>avg_likes_by_page',
                'format' => 'test_format',
                'gap_policy' => 10,
                'percents' => [10, 80],
                'keyed' => false,
            ],
        ];

        $this->assertEquals($expected, $serialDiffAgg->toArray());
    }

    /**
     * @group unit
     */
    public function testToArrayInvalidBucketsPath(): void
    {
        $this->expectException(InvalidException::class);

        $serialDiffAgg = new PercentilesBucket('avg_bucket');
        $serialDiffAgg->toArray();
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            Document::create(['page' => 1, 'likes' => 5]),
            Document::create(['page' => 2, 'likes' => 100]),
            Document::create(['page' => 3, 'likes' => 150]),
        ]);

        $index->refresh();

        return $index;
    }
}
