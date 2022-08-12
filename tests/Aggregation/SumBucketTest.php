<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\GapPolicyInterface;
use Elastica\Aggregation\Sum;
use Elastica\Aggregation\SumBucket;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Index;
use Elastica\Query;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * @internal
 */
class SumBucketTest extends BaseAggregationTest
{
    use ExpectDeprecationTrait;

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
                new SumBucket('sum_likes_by_page', 'pages>sum_likes')
            )
        ;

        $results = $this->getIndexForTest()->search($query)->getAggregations();

        $this->assertEquals(336, $results['pages']['buckets'][0]['sum_likes']['value']);
        $this->assertEquals(155, $results['pages']['buckets'][1]['sum_likes']['value']);
        $this->assertEquals(491, $results['sum_likes_by_page']['value']);
    }

    /**
     * @group unit
     */
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

    /**
     * @group unit
     * @group legacy
     */
    public function testLegacyConstructWithNoBucketsPath(): void
    {
        $this->expectDeprecation('Since ruflin/elastica 7.1.3: Not passing a 2nd argument to "Elastica\Aggregation\SumBucket::__construct()" is deprecated, pass a string instead. It will be removed in 8.0.');

        new SumBucket('sum_bucket');
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testLegacyConstructWithNullBucketsPath(): void
    {
        $this->expectDeprecation('Since ruflin/elastica 7.1.3: Passing null as 2nd argument to "Elastica\Aggregation\SumBucket::__construct()" is deprecated, pass a string instead. It will be removed in 8.0.');

        new SumBucket('sum_bucket', null);
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testLegacyToArrayWithNoBucketsPath(): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('Buckets path is required');

        (new SumBucket('sum_bucket'))->toArray();
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
