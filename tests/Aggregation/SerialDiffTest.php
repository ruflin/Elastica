<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\DateHistogram;
use Elastica\Aggregation\GapPolicyInterface;
use Elastica\Aggregation\Max;
use Elastica\Aggregation\SerialDiff;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class SerialDiffTest extends BaseAggregationTestCase
{
    #[Group('functional')]
    public function testSerialDiffAggregation(): void
    {
        $dateHistogramAggregation = new DateHistogram('measurements', 'measured_at', 'hour');

        $dateHistogramAggregation
            ->addAggregation((new Max('max_value'))->setField('value'))
            ->addAggregation(new SerialDiff('result', 'max_value'))
        ;

        $query = Query::create([])->addAggregation($dateHistogramAggregation);

        $results = $this->getIndexForTest()->search($query)->getAggregation('measurements');

        $this->assertEquals(false, isset($results['buckets'][0]['result']['value']));
        $this->assertEquals(166, $results['buckets'][1]['result']['value']);
        $this->assertEquals(84, $results['buckets'][2]['result']['value']);
        $this->assertEquals(121, $results['buckets'][3]['result']['value']);
    }

    #[Group('unit')]
    public function testConstructThroughSetters(): void
    {
        $aggregation = (new SerialDiff('difference', 'nested_agg'))
            ->setFormat('test_format')
            ->setGapPolicy(GapPolicyInterface::KEEP_VALUES)
            ->setLag(5)
        ;

        $expected = [
            'serial_diff' => [
                'buckets_path' => 'nested_agg',
                'format' => 'test_format',
                'gap_policy' => 'keep_values',
                'lag' => 5,
            ],
        ];

        $this->assertEquals($expected, $aggregation->toArray());
    }

    private function getIndexForTest(): Index
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
        ], ['refresh' => 'true']);

        return $index;
    }
}
