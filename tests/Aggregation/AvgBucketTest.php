<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Avg;
use Elastica\Aggregation\AvgBucket;
use Elastica\Aggregation\GapPolicyInterface;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class AvgBucketTest extends BaseAggregationTestCase
{
    #[Group('functional')]
    public function testAvgBucketAggregation(): void
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
                new AvgBucket('avg_likes_by_page', 'pages>avg_likes')
            )
        ;

        $results = $this->getIndexForTest()->search($query)->getAggregations();

        $this->assertEquals(168, $results['pages']['buckets'][0]['avg_likes']['value']);
        $this->assertEquals(155, $results['pages']['buckets'][1]['avg_likes']['value']);
        $this->assertEquals(161.5, $results['avg_likes_by_page']['value']);
    }

    #[Group('unit')]
    public function testConstructThroughSetters(): void
    {
        $aggregation = (new AvgBucket('avg_bucket', 'pages>avg_likes_by_page'))
            ->setFormat('test_format')
            ->setGapPolicy(GapPolicyInterface::INSERT_ZEROS)
        ;

        $expected = [
            'avg_bucket' => [
                'buckets_path' => 'pages>avg_likes_by_page',
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
        ], ['refresh' => 'true']);

        return $index;
    }
}
