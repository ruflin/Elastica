<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\DateHistogram;
use Elastica\Aggregation\Max;
use Elastica\Aggregation\SerialDiff;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;

/**
 * @internal
 */
class SerialDiffTest extends BaseAggregationTest
{
    /**
     * @group functional
     */
    public function testSerialDiffAggregation(): void
    {
        $dateHistogramAggregation = new DateHistogram('measurements', 'measured_at', 'hour');

        $dateHistogramAggregation
            ->addAggregation((new Max('max_value'))->setField('value'))
            ->addAggregation(new SerialDiff('result', 'max_value'))
        ;

        $query = Query::create([])->addAggregation($dateHistogramAggregation);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('measurements');

        $this->assertEquals(false, isset($results['buckets'][0]['result']['value']));
        $this->assertEquals(166, $results['buckets'][1]['result']['value']);
        $this->assertEquals(84, $results['buckets'][2]['result']['value']);
        $this->assertEquals(121, $results['buckets'][3]['result']['value']);
    }

    /**
     * @group unit
     */
    public function testConstructThroughSetters(): void
    {
        $serialDiffAgg = new SerialDiff('difference');

        $serialDiffAgg
            ->setBucketsPath('nested_agg')
            ->setFormat('test_format')
            ->setGapPolicy(10)
            ->setLag(5)
        ;

        $expected = [
            'serial_diff' => [
                'buckets_path' => 'nested_agg',
                'format' => 'test_format',
                'gap_policy' => 10,
                'lag' => 5,
            ],
        ];

        $this->assertEquals($expected, $serialDiffAgg->toArray());
    }

    /**
     * @group unit
     */
    public function testToArrayInvalidBucketsPath(): void
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);

        $serialDiffAgg = new SerialDiff('difference');
        $serialDiffAgg->toArray();
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->setMapping(Mapping::create([
            'value' => ['type' => 'long'],
            'measured_at' => ['type' => 'date'],
        ]));

        $index->addDocuments([
            Document::create(['value' => 100, 'measured_at' => '2016-08-23T15:00:00+0200']),
            Document::create(['value' => 266, 'measured_at' => '2016-08-23T16:00:00+0200']),
            Document::create(['value' => 350, 'measured_at' => '2016-08-23T17:00:00+0200']),
            Document::create(['value' => 471, 'measured_at' => '2016-08-23T18:00:00+0200']),
        ]);

        $index->refresh();

        return $index;
    }
}
