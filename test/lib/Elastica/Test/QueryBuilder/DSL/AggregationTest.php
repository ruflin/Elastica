<?php
namespace Elastica\Test\QueryBuilder\DSL;

use Elastica\Filter\Exists;
use Elastica\Query\Term;
use Elastica\QueryBuilder\DSL;

class AggregationTest extends AbstractDSLTest
{
    /**
     * @group unit
     */
    public function testType()
    {
        $aggregationDSL = new DSL\Aggregation();

        $this->assertInstanceOf('Elastica\QueryBuilder\DSL', $aggregationDSL);
        $this->assertEquals(DSL::TYPE_AGGREGATION, $aggregationDSL->getType());
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testFilteredInvalid()
    {
        $queryDSL = new DSL\Aggregation();
        $queryDSL->filter(null, $this);
    }

    /**
     * @group unit
     */
    public function testInterface()
    {
        $aggregationDSL = new DSL\Aggregation();

        $this->_assertImplemented($aggregationDSL, 'avg', 'Elastica\Aggregation\Avg', ['name']);
        $this->_assertImplemented($aggregationDSL, 'cardinality', 'Elastica\Aggregation\Cardinality', ['name']);
        $this->_assertImplemented($aggregationDSL, 'date_histogram', 'Elastica\Aggregation\DateHistogram', ['name', 'field', 1]);
        $this->_assertImplemented($aggregationDSL, 'date_range', 'Elastica\Aggregation\DateRange', ['name']);
        $this->_assertImplemented($aggregationDSL, 'extended_stats', 'Elastica\Aggregation\ExtendedStats', ['name']);
        $this->hideDeprecated();
        $this->_assertImplemented($aggregationDSL, 'filter', 'Elastica\Aggregation\Filter', ['name', new Exists('field')]);
        $this->showDeprecated();

        $this->_assertImplemented($aggregationDSL, 'filter', 'Elastica\Aggregation\Filter', ['name', new Term()]);

        $this->_assertImplemented($aggregationDSL, 'filters', 'Elastica\Aggregation\Filters', ['name']);
        $this->_assertImplemented($aggregationDSL, 'geo_distance', 'Elastica\Aggregation\GeoDistance', ['name', 'field', 'origin']);
        $this->_assertImplemented($aggregationDSL, 'geohash_grid', 'Elastica\Aggregation\GeohashGrid', ['name', 'field']);
        $this->_assertImplemented($aggregationDSL, 'global_agg', 'Elastica\Aggregation\GlobalAggregation', ['name']);
        $this->_assertImplemented($aggregationDSL, 'histogram', 'Elastica\Aggregation\Histogram', ['name', 'field', 1]);
        $this->_assertImplemented($aggregationDSL, 'ipv4_range', 'Elastica\Aggregation\IpRange', ['name', 'field']);
        $this->_assertImplemented($aggregationDSL, 'max', 'Elastica\Aggregation\Max', ['name']);
        $this->_assertImplemented($aggregationDSL, 'min', 'Elastica\Aggregation\Min', ['name']);
        $this->_assertImplemented($aggregationDSL, 'missing', 'Elastica\Aggregation\Missing', ['name', 'field']);
        $this->_assertImplemented($aggregationDSL, 'nested', 'Elastica\Aggregation\Nested', ['name', 'path']);
        $this->_assertImplemented($aggregationDSL, 'percentiles', 'Elastica\Aggregation\Percentiles', ['name']);
        $this->_assertImplemented($aggregationDSL, 'range', 'Elastica\Aggregation\Range', ['name']);
        $this->_assertImplemented($aggregationDSL, 'reverse_nested', 'Elastica\Aggregation\ReverseNested', ['name']);
        $this->_assertImplemented($aggregationDSL, 'scripted_metric', 'Elastica\Aggregation\ScriptedMetric', ['name']);
        $this->_assertImplemented($aggregationDSL, 'significant_terms', 'Elastica\Aggregation\SignificantTerms', ['name']);
        $this->_assertImplemented($aggregationDSL, 'stats', 'Elastica\Aggregation\Stats', ['name']);
        $this->_assertImplemented($aggregationDSL, 'sum', 'Elastica\Aggregation\Sum', ['name']);
        $this->_assertImplemented($aggregationDSL, 'terms', 'Elastica\Aggregation\Terms', ['name']);
        $this->_assertImplemented($aggregationDSL, 'top_hits', 'Elastica\Aggregation\TopHits', ['name']);
        $this->_assertImplemented($aggregationDSL, 'value_count', 'Elastica\Aggregation\ValueCount', ['name', 'field']);
        $this->_assertImplemented($aggregationDSL, 'bucket_script', 'Elastica\Aggregation\BucketScript', ['name']);
        $this->_assertImplemented($aggregationDSL, 'serial_diff', 'Elastica\Aggregation\SerialDiff', ['name']);

        $this->_assertNotImplemented($aggregationDSL, 'children', ['name']);
        $this->_assertNotImplemented($aggregationDSL, 'geo_bounds', ['name']);
        $this->_assertNotImplemented($aggregationDSL, 'percentile_ranks', ['name']);
    }
}
