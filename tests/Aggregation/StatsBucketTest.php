<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\GapPolicyInterface;
use Elastica\Aggregation\Histogram;
use Elastica\Aggregation\Max;
use Elastica\Aggregation\StatsBucket;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class StatsBucketTest extends BaseAggregationTestCase
{
    #[Group('functional')]
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

    #[Group('unit')]
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
