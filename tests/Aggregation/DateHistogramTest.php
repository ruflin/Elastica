<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\DateHistogram;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;

/**
 * @internal
 */
class DateHistogramTest extends BaseAggregationTest
{
    /**
     * @group functional
     */
    public function testDateHistogramAggregation(): void
    {
        $agg = new DateHistogram('hist', 'created', '1h');

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
     */
    public function testSetOffset(): void
    {
        $agg = new DateHistogram('hist', 'created', '1h');

        $agg->setOffset('3m');

        $expected = [
            'date_histogram' => [
                'field' => 'created',
                'interval' => '1h',
                'offset' => '3m',
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
        $this->_checkVersion('1.5');

        $agg = new DateHistogram('hist', 'created', '1m');
        $agg->setOffset('+40s');

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
        $agg = new DateHistogram('hist', 'created', '1h');

        $agg->setTimezone('-02:30');

        $expected = [
            'date_histogram' => [
                'field' => 'created',
                'interval' => '1h',
                'time_zone' => '-02:30',
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
        ]);

        $index->refresh();

        return $index;
    }
}
