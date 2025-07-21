<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\BucketScript;
use Elastica\Aggregation\GapPolicyInterface;
use Elastica\Aggregation\Histogram;
use Elastica\Aggregation\Max;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class BucketScriptTest extends BaseAggregationTestCase
{
    #[Group('functional')]
    public function testBucketScriptAggregation(): void
    {
        $bucketScriptAggregation = new BucketScript(
            'result',
            [
                'divisor' => 'max_weight',
                'dividend' => 'max_height',
            ],
            'params.dividend / params.divisor'
        );

        $histogramAggregation = new Histogram('age_groups', 'age', 10);

        $histogramAggregation
            ->addAggregation((new Max('max_weight'))->setField('weight'))
            ->addAggregation((new Max('max_height'))->setField('height'))
            ->addAggregation($bucketScriptAggregation)
        ;

        $query = Query::create([])->addAggregation($histogramAggregation);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('age_groups');

        $this->assertEquals(3, $results['buckets'][0]['result']['value']);
        $this->assertEquals(2.4, $results['buckets'][1]['result']['value']);
        $this->assertEquals(3.1, $results['buckets'][2]['result']['value']);
    }

    #[Group('unit')]
    public function testConstructThroughSetters(): void
    {
        $serialDiffAgg = new BucketScript('bucket_scripted', [
            'x' => 'agg_max',
            'y' => 'agg_sum',
            'z' => 'agg_min',
        ], 'x / y * z');

        $serialDiffAgg
            ->setFormat('test_format')
            ->setGapPolicy(GapPolicyInterface::INSERT_ZEROS)
        ;

        $expected = [
            'bucket_script' => [
                'script' => 'x / y * z',
                'buckets_path' => [
                    'x' => 'agg_max',
                    'y' => 'agg_sum',
                    'z' => 'agg_min',
                ],
                'format' => 'test_format',
                'gap_policy' => GapPolicyInterface::INSERT_ZEROS,
            ],
        ];

        $this->assertEquals($expected, $serialDiffAgg->toArray());
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            Document::create(['weight' => 60, 'height' => 180, 'age' => 25]),
            Document::create(['weight' => 65, 'height' => 156, 'age' => 32]),
            Document::create(['weight' => 50, 'height' => 155, 'age' => 45]),
        ]);

        $index->refresh();

        return $index;
    }
}
