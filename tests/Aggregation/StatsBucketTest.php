<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\GapPolicyInterface;
use Elastica\Aggregation\Histogram;
use Elastica\Aggregation\Max;
use Elastica\Aggregation\StatsBucket;
use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Index;
use Elastica\Query;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * @internal
 */
class StatsBucketTest extends BaseAggregationTest
{
    use ExpectDeprecationTrait;

    /**
     * @group functional
     */
    public function testStatBucketAggregation(): void
    {
        $bucketScriptAggregation = new StatsBucket('result', 'age_groups>max_weight');

        $histogramAggregation = (new Histogram('age_groups', 'age', 10))
            ->addAggregation((new Max('max_weight'))->setField('weight'))
        ;

        $histogramAggregation->addAggregation((new Max('max_weight'))->setField('weight'));

        $query = Query::create([])
            ->addAggregation($histogramAggregation)
            ->addAggregation($bucketScriptAggregation)
        ;

        $results = $this->getIndexForTest()->search($query)->getAggregation('result');

        $this->assertEquals(3, $results['count']);
        $this->assertEquals(50, $results['min']);
        $this->assertEquals(70, $results['max']);
        $this->assertEquals(60, $results['avg']);
        $this->assertEquals(180, $results['sum']);
    }

    /**
     * @group unit
     */
    public function testConstructThroughSetters(): void
    {
        $aggregation = (new StatsBucket('bucket_part', 'age_groups>max_weight'))
            ->setFormat('test_format')
            ->setGapPolicy(GapPolicyInterface::INSERT_ZEROS)
        ;

        $expected = [
            'stats_bucket' => [
                'buckets_path' => 'age_groups>max_weight',
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
        $this->expectDeprecation('Since ruflin/elastica 7.1.3: Not passing a 2nd argument to "Elastica\Aggregation\StatsBucket::__construct()" is deprecated, pass a string instead. It will be removed in 8.0.');

        new StatsBucket('stats_bucket');
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testLegacyConstructWithNullBucketsPath(): void
    {
        $this->expectDeprecation('Since ruflin/elastica 7.1.3: Passing null as 2nd argument to "Elastica\Aggregation\StatsBucket::__construct()" is deprecated, pass a string instead. It will be removed in 8.0.');

        new StatsBucket('stats_bucket', null);
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testLegacyToArrayWithNoBucketsPath(): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('Buckets path is required');

        (new StatsBucket('stats_bucket'))->toArray();
    }

    private function getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            Document::create(['weight' => 60, 'height' => 180, 'age' => 25]),
            Document::create(['weight' => 70, 'height' => 156, 'age' => 32]),
            Document::create(['weight' => 50, 'height' => 155, 'age' => 45]),
        ], ['refresh' => 'true']);

        return $index;
    }
}
