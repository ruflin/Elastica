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

        $this->_assertImplemented($aggregationDSL, 'avg', 'Elastica\Aggregation\Avg', array('name'));
        $this->_assertImplemented($aggregationDSL, 'cardinality', 'Elastica\Aggregation\Cardinality', array('name'));
        $this->_assertImplemented($aggregationDSL, 'date_histogram', 'Elastica\Aggregation\DateHistogram', array('name', 'field', 1));
        $this->_assertImplemented($aggregationDSL, 'date_range', 'Elastica\Aggregation\DateRange', array('name'));
        $this->_assertImplemented($aggregationDSL, 'extended_stats', 'Elastica\Aggregation\ExtendedStats', array('name'));
        $this->hideDeprecated();
        $this->_assertImplemented($aggregationDSL, 'filter', 'Elastica\Aggregation\Filter', array('name', new Exists('field')));
        $this->showDeprecated();

        $this->_assertImplemented($aggregationDSL, 'filter', 'Elastica\Aggregation\Filter', array('name', new Term()));

        $this->_assertImplemented($aggregationDSL, 'filters', 'Elastica\Aggregation\Filters', array('name'));
        $this->_assertImplemented($aggregationDSL, 'geo_distance', 'Elastica\Aggregation\GeoDistance', array('name', 'field', 'origin'));
        $this->_assertImplemented($aggregationDSL, 'geohash_grid', 'Elastica\Aggregation\GeohashGrid', array('name', 'field'));
        $this->_assertImplemented($aggregationDSL, 'global_agg', 'Elastica\Aggregation\GlobalAggregation', array('name'));
        $this->_assertImplemented($aggregationDSL, 'histogram', 'Elastica\Aggregation\Histogram', array('name', 'field', 1));
        $this->_assertImplemented($aggregationDSL, 'ipv4_range', 'Elastica\Aggregation\IpRange', array('name', 'field'));
        $this->_assertImplemented($aggregationDSL, 'max', 'Elastica\Aggregation\Max', array('name'));
        $this->_assertImplemented($aggregationDSL, 'min', 'Elastica\Aggregation\Min', array('name'));
        $this->_assertImplemented($aggregationDSL, 'missing', 'Elastica\Aggregation\Missing', array('name', 'field'));
        $this->_assertImplemented($aggregationDSL, 'nested', 'Elastica\Aggregation\Nested', array('name', 'path'));
        $this->_assertImplemented($aggregationDSL, 'percentiles', 'Elastica\Aggregation\Percentiles', array('name'));
        $this->_assertImplemented($aggregationDSL, 'range', 'Elastica\Aggregation\Range', array('name'));
        $this->_assertImplemented($aggregationDSL, 'reverse_nested', 'Elastica\Aggregation\ReverseNested', array('name'));
        $this->_assertImplemented($aggregationDSL, 'scripted_metric', 'Elastica\Aggregation\ScriptedMetric', array('name'));
        $this->_assertImplemented($aggregationDSL, 'significant_terms', 'Elastica\Aggregation\SignificantTerms', array('name'));
        $this->_assertImplemented($aggregationDSL, 'stats', 'Elastica\Aggregation\Stats', array('name'));
        $this->_assertImplemented($aggregationDSL, 'sum', 'Elastica\Aggregation\Sum', array('name'));
        $this->_assertImplemented($aggregationDSL, 'terms', 'Elastica\Aggregation\Terms', array('name'));
        $this->_assertImplemented($aggregationDSL, 'top_hits', 'Elastica\Aggregation\TopHits', array('name'));
        $this->_assertImplemented($aggregationDSL, 'value_count', 'Elastica\Aggregation\ValueCount', array('name', 'field'));

        $this->_assertNotImplemented($aggregationDSL, 'children', array('name'));
        $this->_assertNotImplemented($aggregationDSL, 'geo_bounds', array('name'));
        $this->_assertNotImplemented($aggregationDSL, 'percentile_ranks', array('name'));
    }
}
