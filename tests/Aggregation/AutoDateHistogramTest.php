<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\AutoDateHistogram;
use Elastica\Query;

/**
 * @internal
 */
class AutoDateHistogramTest extends BaseAggregationTest
{
    /**
     * @group unit
     */
    public function testAvgBucketAggregation(): void
    {
        $aggregationKey = 'aggs';
        $aggregationType = 'auto_date_histogram';
        $aggregationName = 'aggregation_name';
        $fieldName = 'field_name';
        $format = 'yyyy-MM-dd';
        $timeZone = '-01:00';
        $minimumInterval = 'minute';

        $aggregation = (new AutoDateHistogram('aggregation_name', 'field_name'))
            ->setBuckets(10)
            ->setFormat('yyyy-MM-dd')
            ->setTimezone('-01:00')
            ->setMinimumInterval('minute')
        ;

        $query = Query::create([])->addAggregation($aggregation);

        $queryArray = $query->toArray();
        $this->assertTrue(isset($queryArray[$aggregationKey][$aggregationName][$aggregationType]['field']));
        $this->assertEquals($fieldName, $queryArray[$aggregationKey][$aggregationName][$aggregationType]['field']);
        $this->assertTrue(isset($queryArray[$aggregationKey][$aggregationName][$aggregationType]['format']));
        $this->assertEquals($format, $queryArray[$aggregationKey][$aggregationName][$aggregationType]['format']);
        $this->assertTrue(isset($queryArray[$aggregationKey][$aggregationName][$aggregationType]['time_zone']));
        $this->assertEquals($timeZone, $queryArray[$aggregationKey][$aggregationName][$aggregationType]['time_zone']);
        $this->assertTrue(isset($queryArray[$aggregationKey][$aggregationName][$aggregationType]['minimum_interval']));
        $this->assertEquals(
            $minimumInterval,
            $queryArray[$aggregationKey][$aggregationName][$aggregationType]['minimum_interval']
        );
    }
}
