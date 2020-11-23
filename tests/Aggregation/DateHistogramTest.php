<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\DateHistogram;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;

/**
 * @internal
 */
class DateHistogramTest extends BaseAggregationTest
{
    use ExpectDeprecationTrait;

    /**
     * @group functional
     */
    public function testDateHistogramAggregation(): void
    {
        $agg = new DateHistogram('hist', 'created');

        $version = $this->_getVersion();

        if (\version_compare($version, '7.2') < 0) {
            $agg->setParam('interval', '1h');
        } else {
            $agg->setFixedInterval('1h');
        }

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('hist');

        $docCount = 0;
        $nonDocCount = 0;
        foreach ($results['buckets'] as $bucket) {
            if (1 == $bucket['doc_count']) {
                ++$docCount;
            } else {
                ++$nonDocCount;
            }
        }
        // 3 Documents that were added
        $this->assertEquals(3, $docCount);
        // 1 document that was generated in between for the missing hour
        $this->assertEquals(1, $nonDocCount);
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testDateHistogramAggregationWithIntervalTriggersADeprecation(): void
    {
        $this->expectDeprecation('Since ruflin/elastica 7.1.0: Argument 3 passed to "Elastica\Aggregation\DateHistogram::__construct()" is deprecated, use "setDateInterval()" or "setCalendarInterval()" instead. It will be removed in 8.0.');
        new DateHistogram('hist', 'created', 'day');
    }

    /**
     * @group unit
     * @group legacy
     */
    public function testDateHistogramAggregationSetIntervalTriggersADeprecation(): void
    {
        $agg = new DateHistogram('hist', 'created');

        $this->expectDeprecation('Since ruflin/elastica 7.1.0: The "Elastica\Aggregation\DateHistogram::setInterval()" method is deprecated, use "setDateInterval()" or "setCalendarInterval()" instead. It will be removed in 8.0.');

        $agg->setInterval('day');
    }

    /**
     * @group functional
     */
    public function testDateHistogramCalendarAggregation(): void
    {
        $agg = new DateHistogram('hist', 'created');

        $version = $this->_getVersion();

        if (\version_compare($version, '7.2') < 0) {
            $agg->setParam('interval', '1h');
        } else {
            $agg->setCalendarInterval('1h');
        }

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('hist');

        $docCount = 0;
        $nonDocCount = 0;
        foreach ($results['buckets'] as $bucket) {
            if (1 == $bucket['doc_count']) {
                ++$docCount;
            } else {
                ++$nonDocCount;
            }
        }
        // 3 Documents that were added
        $this->assertEquals(3, $docCount);
        // 1 document that was generated in between for the missing hour
        $this->assertEquals(1, $nonDocCount);
    }

    /**
     * @group functional
     */
    public function testDateHistogramAggregationWithMissing(): void
    {
        $agg = new DateHistogram('hist', 'created');

        $version = $this->_getVersion();

        if (\version_compare($version, '7.2') < 0) {
            $agg->setParam('interval', '1h');
        } else {
            $agg->setFixedInterval('1h');
        }

        $agg->setMissing('2014-01-29T04:20:00');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('hist');

        $docCount = 0;
        $nonDocCount = 0;
        foreach ($results['buckets'] as $bucket) {
            if (1 == $bucket['doc_count']) {
                ++$docCount;
            } else {
                ++$nonDocCount;
            }
        }
        // 3 Documents that were added
        $this->assertEquals(4, $docCount);
        // 1 document that was generated in between for the missing hour
        $this->assertEquals(1, $nonDocCount);
    }

    /**
     * @group functional
     */
    public function testDateHistogramKeyedAggregation(): void
    {
        $agg = new DateHistogram('hist', 'created');

        $version = $this->_getVersion();

        if (\version_compare($version, '7.2') < 0) {
            $agg->setParam('interval', '1h');
        } else {
            $agg->setFixedInterval('1h');
        }

        $agg->setKeyed();

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('hist');

        $expected = [
            '2014-01-29T00:00:00.000Z',
            '2014-01-29T01:00:00.000Z',
            '2014-01-29T02:00:00.000Z',
            '2014-01-29T03:00:00.000Z',
        ];
        $this->assertSame($expected, \array_keys($results['buckets']));
    }

    /**
     * @group unit
     */
    public function testSetOffset(): void
    {
        $agg = new DateHistogram('hist', 'created');
        $agg->setFixedInterval('1h');

        $agg->setOffset('3m');

        $expected = [
            'date_histogram' => [
                'field' => 'created',
                'offset' => '3m',
                'fixed_interval' => '1h',
            ],
        ];

        $this->assertEquals($expected, $agg->toArray());

        $this->assertInstanceOf(DateHistogram::class, $agg->setOffset('3m'));
    }

    /**
     * @group functional
     */
    public function testSetOffsetWorks(): void
    {
        $agg = new DateHistogram('hist', 'created');
        $agg->setOffset('+40s');

        $version = $this->_getVersion();

        if (\version_compare($version, '7.2') < 0) {
            $agg->setParam('interval', '1m');
        } else {
            $agg->setFixedInterval('1m');
        }

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('hist');

        $this->assertEquals('2014-01-29T00:19:40.000Z', $results['buckets'][0]['key_as_string']);
    }

    /**
     * @group unit
     */
    public function testSetTimezone(): void
    {
        $agg = new DateHistogram('hist', 'created');
        $agg->setFixedInterval('1h');

        $agg->setTimezone('-02:30');

        $expected = [
            'date_histogram' => [
                'field' => 'created',
                'time_zone' => '-02:30',
                'fixed_interval' => '1h',
            ],
        ];

        $this->assertEquals($expected, $agg->toArray());

        $this->assertInstanceOf(DateHistogram::class, $agg->setTimezone('-02:30'));
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'created' => ['type' => 'date'],
        ]));

        $index->addDocuments([
            new Document(1, ['created' => '2014-01-29T00:20:00']),
            new Document(2, ['created' => '2014-01-29T02:20:00']),
            new Document(3, ['created' => '2014-01-29T03:20:00']),
            new Document(4, ['anything' => 'anything']),
        ]);

        $index->refresh();

        return $index;
    }
}
