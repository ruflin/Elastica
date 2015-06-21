<?php
namespace Elastica\Test\QueryBuilder\DSL;

use Elastica\Filter\Exists;
use Elastica\Query\Match;
use Elastica\QueryBuilder\DSL;

class FilterTest extends AbstractDSLTest
{
    /**
     * @group unit
     */
    public function testType()
    {
        $filterDSL = new DSL\Filter();

        $this->assertInstanceOf('Elastica\QueryBuilder\DSL', $filterDSL);
        $this->assertEquals(DSL::TYPE_FILTER, $filterDSL->getType());
    }

    /**
     * @group unit
     */
    public function testInterface()
    {
        $filterDSL = new DSL\Filter();

        $this->_assertImplemented($filterDSL, 'bool', 'Elastica\Filter\BoolFilter', array());
        $this->_assertImplemented($filterDSL, 'bool_and', 'Elastica\Filter\BoolAnd', array(array(new Exists('field'))));
        $this->_assertImplemented($filterDSL, 'bool_not', 'Elastica\Filter\BoolNot', array(new Exists('field')));
        $this->_assertImplemented($filterDSL, 'bool_or', 'Elastica\Filter\BoolOr', array(array(new Exists('field'))));
        $this->_assertImplemented($filterDSL, 'exists', 'Elastica\Filter\Exists', array('field'));
        $this->_assertImplemented($filterDSL, 'geo_bounding_box', 'Elastica\Filter\GeoBoundingBox', array('field', array(1, 2)));
        $this->_assertImplemented($filterDSL, 'geo_distance', 'Elastica\Filter\GeoDistance', array('key', 'location', 'distance'));
        $this->_assertImplemented($filterDSL, 'geo_distance_range', 'Elastica\Filter\GeoDistanceRange', array('key', 'location'));
        $this->_assertImplemented($filterDSL, 'geo_polygon', 'Elastica\Filter\GeoPolygon', array('key', array()));
        $this->_assertImplemented($filterDSL, 'geo_shape_pre_indexed', 'Elastica\Filter\GeoShapePreIndexed', array('path', 'indexedId', 'indexedType', 'indexedIndex', 'indexedPath'));
        $this->_assertImplemented($filterDSL, 'geo_shape_provided', 'Elastica\Filter\GeoShapeProvided', array('path', array()));
        $this->_assertImplemented($filterDSL, 'geohash_cell', 'Elastica\Filter\GeohashCell', array('field', 'location'));
        $this->_assertImplemented($filterDSL, 'has_child', 'Elastica\Filter\HasChild', array(new Match(), 'type'));
        $this->_assertImplemented($filterDSL, 'has_parent', 'Elastica\Filter\HasParent', array(new Match(), 'type'));
        $this->_assertImplemented($filterDSL, 'ids', 'Elastica\Filter\Ids', array('type', array()));
        $this->_assertImplemented($filterDSL, 'indices', 'Elastica\Filter\Indices', array(new Exists('field'), array()));
        $this->_assertImplemented($filterDSL, 'limit', 'Elastica\Filter\Limit', array(1));
        $this->_assertImplemented($filterDSL, 'match_all', 'Elastica\Filter\MatchAll', array());
        $this->_assertImplemented($filterDSL, 'missing', 'Elastica\Filter\Missing', array('field'));
        $this->_assertImplemented($filterDSL, 'nested', 'Elastica\Filter\Nested', array());
        $this->_assertImplemented($filterDSL, 'numeric_range', 'Elastica\Filter\NumericRange', array());
        $this->_assertImplemented($filterDSL, 'prefix', 'Elastica\Filter\Prefix', array('field', 'prefix'));
        $this->_assertImplemented($filterDSL, 'query', 'Elastica\Filter\Query', array(new Match()));
        $this->_assertImplemented($filterDSL, 'range', 'Elastica\Filter\Range', array('field', array()));
        $this->_assertImplemented($filterDSL, 'regexp', 'Elastica\Filter\Regexp', array('field', 'regex'));
        $this->_assertImplemented($filterDSL, 'script', 'Elastica\Filter\Script', array('script'));
        $this->_assertImplemented($filterDSL, 'term', 'Elastica\Filter\Term', array());
        $this->_assertImplemented($filterDSL, 'terms', 'Elastica\Filter\Terms', array('field', array()));
        $this->_assertImplemented($filterDSL, 'type', 'Elastica\Filter\Type', array('type'));
    }
}
