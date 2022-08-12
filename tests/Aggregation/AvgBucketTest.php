<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Avg;
use Elastica\Aggregation\AvgBucket;
use Elastica\Aggregation\GapPolicyInterface;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Index;
use Elastica\Query;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * @internal
 */
class AvgBucketTest extends BaseAggregationTest
{
    use ExpectDeprecationTrait;

    /**
     * @group functional
     */
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

    /**
     * @group unit
     */
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

    /**
     * @group unit
     * @group legacy
     */
    public function testLegacyConstructWithNoBucketsPath(): void
    {
        $this->expectDeprecation('Since ruflin/elastica 7.1.3: Not passing a 2nd argument to "Elastica\Aggregation\AvgBucket::__construct()" is deprecated, pass a string instead. It will be removed in 8.0.');

        new AvgBucket('avg_bucket');
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testLegacyConstructWithNullBucketsPath(): void
    {
        $this->expectDeprecation('Since ruflin/elastica 7.1.3: Passing null as 2nd argument to "Elastica\Aggregation\AvgBucket::__construct()" is deprecated, pass a string instead. It will be removed in 8.0.');

        new AvgBucket('avg_bucket', null);
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testLegacyToArrayWithNoBucketsPath(): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('Buckets path is required');

        (new AvgBucket('avg_bucket'))->toArray();
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
