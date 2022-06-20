<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Avg;
use Elastica\Aggregation\GapPolicyInterface;
use Elastica\Aggregation\PercentilesBucket;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Index;
use Elastica\Query;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * @internal
 */
class PercentilesBucketTest extends BaseAggregationTest
{
    use ExpectDeprecationTrait;

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
                (new PercentilesBucket('percentiles_likes_by_page', 'pages>avg_likes'))
                    ->setPercents([5, 50, 95])
            )
        ;

        $results = $this->getIndexForTest()->search($query)->getAggregations();

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
        $aggregation = (new PercentilesBucket('percentiles_bucket', 'pages>avg_likes_by_page'))
            ->setFormat('test_format')
            ->setPercents([10, 80])
            ->setGapPolicy(GapPolicyInterface::INSERT_ZEROS)
            ->setKeyed(false)
        ;

        $expected = [
            'percentiles_bucket' => [
                'buckets_path' => 'pages>avg_likes_by_page',
                'format' => 'test_format',
                'gap_policy' => GapPolicyInterface::INSERT_ZEROS,
                'percents' => [10, 80],
                'keyed' => false,
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
        $this->expectDeprecation('Since ruflin/elastica 7.1.3: Not passing a 2nd argument to "Elastica\Aggregation\PercentilesBucket::__construct()" is deprecated, pass a string instead. It will be removed in 8.0.');

        new PercentilesBucket('percentiles_bucket');
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testLegacyConstructWithNullBucketsPath(): void
    {
        $this->expectDeprecation('Since ruflin/elastica 7.1.3: Passing null as 2nd argument to "Elastica\Aggregation\PercentilesBucket::__construct()" is deprecated, pass a string instead. It will be removed in 8.0.');

        new PercentilesBucket('percentiles_bucket', null);
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testLegacyToArrayWithNoBucketsPath(): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('Buckets path is required');

        (new PercentilesBucket('percentiles_bucket'))->toArray();
    }

    private function getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            Document::create(['page' => 1, 'likes' => 5]),
            Document::create(['page' => 2, 'likes' => 100]),
            Document::create(['page' => 3, 'likes' => 150]),
        ], ['refresh' => 'true']);

        return $index;
    }
}
