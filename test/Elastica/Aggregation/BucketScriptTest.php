<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\BucketScript;
use Elastica\Aggregation\Histogram;
use Elastica\Aggregation\Max;
use Elastica\Document;
use Elastica\Query;

class BucketScriptTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        $index->getType('test')->addDocuments([
            Document::create(['weight' => 60, 'height' => 180, 'age' => 25]),
            Document::create(['weight' => 65, 'height' => 156, 'age' => 32]),
            Document::create(['weight' => 50, 'height' => 155, 'age' => 45]),
        ]);

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testBucketScriptAggregation()
    {
        $this->_checkScriptInlineSetting();

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
            ->addAggregation($bucketScriptAggregation);

        $query = Query::create([])->addAggregation($histogramAggregation);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('age_groups');

        $this->assertEquals(3, $results['buckets'][0]['result']['value']);
        $this->assertEquals(2.4, $results['buckets'][1]['result']['value']);
        $this->assertEquals(3.1, $results['buckets'][2]['result']['value']);
    }

    /**
     * @group unit
     */
    public function testConstructThroughSetters()
    {
        $serialDiffAgg = new BucketScript('bucket_scripted');

        $serialDiffAgg
            ->setScript('x / y * z')
            ->setBucketsPath([
                'x' => 'agg_max',
                'y' => 'agg_sum',
                'z' => 'agg_min',
            ])
            ->setFormat('test_format')
            ->setGapPolicy(10);

        $expected = [
            'bucket_script' => [
                'script' => 'x / y * z',
                'buckets_path' => [
                    'x' => 'agg_max',
                    'y' => 'agg_sum',
                    'z' => 'agg_min',
                ],
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
        $serialDiffAgg = new BucketScript('bucket_scripted');
        $serialDiffAgg->toArray();
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testToArrayInvalidScript()
    {
        $serialDiffAgg = new BucketScript('bucket_scripted', ['path' => 'agg']);
        $serialDiffAgg->toArray();
    }
}
