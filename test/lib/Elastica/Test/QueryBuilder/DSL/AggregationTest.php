<?php
namespace Elastica\Test\QueryBuilder\DSL;

use Elastica\Filter\Exists;
use Elastica\QueryBuilder\DSL;
use Elastica\Test\Base as BaseTest;

class AggregationTest extends BaseTest
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
     */
    public function testAggregations()
    {
        $aggregationDSL = new DSL\Aggregation();

        foreach ($this->_getAggregations() as $methodName => $arguments) {
            $this->assertTrue(
                method_exists($aggregationDSL, $methodName),
                'method for aggregation "'.$methodName.'" not found'
            );

            try {
                $return = call_user_func_array(array($aggregationDSL, $methodName), $arguments);
                $this->assertInstanceOf('Elastica\Aggregation\AbstractAggregation', $return);
            } catch (\Exception $exception) {
                $this->assertInstanceOf(
                    'Elastica\Exception\NotImplementedException',
                    $exception,
                    'breaking change in aggregation "'.$methodName.'" found: '.$exception->getMessage()
                );
            }
        }
    }

    /**
     * @return array
     */
    protected function _getAggregations()
    {
        return array(
            'min' => array('name'),
            'max' => array('name'),
            'sum' => array('name'),
            'avg' => array('name'),
            'stats' => array('name'),
            'extended_stats' => array('name'),
            'value_count' => array('name', 'field'),
            'percentiles' => array('name'),
            'percentile_ranks' => array('name'),
            'cardinality' => array('name'),
            'geo_bounds' => array('name'),
            'top_hits' => array('name'),
            'scripted_metric' => array('name'),
            'global_agg' => array('name'),
            'filters' => array('name'),
            'missing' => array('name', 'field'),
            'nested' => array('name', 'path'),
            'reverse_nested' => array('name'),
            'children' => array('name'),
            'terms' => array('name'),
            'significant_terms' => array('name'),
            'range' => array('name'),
            'date_range' => array('name'),
            'ipv4_range' => array('name', 'field'),
            'histogram' => array('name', 'field', 1),
            'date_histogram' => array('name', 'field', 1),
            'geo_distance' => array('name', 'field', 'origin'),
            'geohash_grid' => array('name', 'field'),
            'filter' => array('name', new Exists('field')),
        );
    }
}
