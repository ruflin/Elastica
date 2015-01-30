<?php

namespace Elastica\Test\QueryBuilder\DSL;

use Elastica\Filter\Exists;
use Elastica\Query\Match;
use Elastica\QueryBuilder\DSL;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array (method name => arguments)
     */
    private $filters = array(
        'bool' => array(),
        'exists' => array('field'),
        'geo_bounding_box' => array('field', array(1, 2)),
        'geo_distance' => array('key', 'location', 'distance'),
        'geo_distance_range' => array('key', 'location'),
        'geo_polygon' => array('key', array()),
        'geo_shape_provided' => array('path', array()),
        'geo_shape_pre_indexed' => array('path', 'indexedId', 'indexedType', 'indexedIndex', 'indexedPath'),
        'geohash_cell' => array('field', 'location'),
        'ids' => array('type', array()),
        'limit' => array(1),
        'match_all' => array(),
        'missing' => array('field'),
        'nested' => array(),
        'numeric_range' => array(),
        'prefix' => array('field', 'prefix'),
        'range' => array('field', array()),
        'regexp' => array('field', 'regex'),
        'script' => array('script'),
        'term' => array(),
        'terms' => array('field', array()),
        'type' => array('type'),
    );

    public function __construct()
    {
        $this->filters['bool_and'] = array(array(new Exists('field')));
        $this->filters['bool_or'] = array(array(new Exists('field')));
        $this->filters['bool_not'] = array(new Exists('field'));
        $this->filters['has_child'] = array(new Match(), 'type');
        $this->filters['has_parent'] = array(new Match(), 'type');
        $this->filters['indices'] = array(new Exists('field'), array());
        $this->filters['query'] = array(new Match());
    }

    public function testType()
    {
        $filterDSL = new DSL\Filter();

        $this->assertInstanceOf('Elastica\QueryBuilder\DSL', $filterDSL);
        $this->assertEquals(DSL::TYPE_FILTER, $filterDSL->getType());
    }

    public function testFilters()
    {
        $filterDSL = new DSL\Filter();

        foreach ($this->filters as $methodName => $arguments) {
            $this->assertTrue(
                method_exists($filterDSL, $methodName),
                'method for filter "'.$methodName.'" not found'
            );

            try {
                $return = call_user_func_array(array($filterDSL, $methodName), $arguments);
                $this->assertInstanceOf('Elastica\Filter\AbstractFilter', $return);
            } catch (\Exception $exception) {
                $this->assertInstanceOf(
                    'Elastica\Exception\NotImplementedException',
                    $exception,
                    'breaking change in filter "'.$methodName.'" found: '.$exception->getMessage()
                );
            }
        }
    }
}
