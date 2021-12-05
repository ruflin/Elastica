<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\DateHistogram;
use Elastica\Aggregation\GapPolicyInterface;
use Elastica\Aggregation\Max;
use Elastica\Aggregation\SerialDiff;
use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * @internal
 */
class SerialDiffTest extends BaseAggregationTest
{
    use ExpectDeprecationTrait;

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

        $results = $this->getIndexForTest()->search($query)->getAggregation('measurements');

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

    /**
     * @group unit
     * @group legacy
     */
    public function testLegacyConstructWithNoBucketsPath(): void
    {
        $this->expectDeprecation('Since ruflin/elastica 7.1.3: Not passing a 2nd argument to "Elastica\Aggregation\SerialDiff::__construct()" is deprecated, pass a string instead. It will be removed in 8.0.');

        new SerialDiff('serial_diff');
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testLegacyConstructWithNullBucketsPath(): void
    {
        $this->expectDeprecation('Since ruflin/elastica 7.1.3: Passing null as 2nd argument to "Elastica\Aggregation\SerialDiff::__construct()" is deprecated, pass a string instead. It will be removed in 8.0.');

        new SerialDiff('serial_diff', null);
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testLegacyToArrayWithNoBucketsPath(): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('Buckets path is required');

        (new SerialDiff('serial_diff'))->toArray();
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
