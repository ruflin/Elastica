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

        $this->hideDeprecated();
        $this->_assertImplemented($filterDSL, 'bool', 'Elastica\Filter\BoolFilter', []);
        $this->_assertImplemented($filterDSL, 'bool_and', 'Elastica\Filter\BoolAnd', [[new Exists('field')]]);
        $this->_assertImplemented($filterDSL, 'bool_not', 'Elastica\Filter\BoolNot', [new Exists('field')]);
        $this->_assertImplemented($filterDSL, 'bool_or', 'Elastica\Filter\BoolOr', [[new Exists('field')]]);
        $this->_assertImplemented($filterDSL, 'exists', 'Elastica\Filter\Exists', ['field']);
        $this->_assertImplemented($filterDSL, 'geo_bounding_box', 'Elastica\Filter\GeoBoundingBox', ['field', [1, 2]]);
        $this->_assertImplemented($filterDSL, 'geo_distance', 'Elastica\Filter\GeoDistance', ['key', 'location', 'distance']);
        $this->_assertImplemented($filterDSL, 'geo_distance_range', 'Elastica\Filter\GeoDistanceRange', ['key', 'location']);
        $this->_assertImplemented($filterDSL, 'geo_polygon', 'Elastica\Filter\GeoPolygon', ['key', []]);
        $this->_assertImplemented($filterDSL, 'geo_shape_pre_indexed', 'Elastica\Filter\GeoShapePreIndexed', ['path', 'indexedId', 'indexedType', 'indexedIndex', 'indexedPath']);
        $this->_assertImplemented($filterDSL, 'geo_shape_provided', 'Elastica\Filter\GeoShapeProvided', ['path', []]);
        $this->_assertImplemented($filterDSL, 'geohash_cell', 'Elastica\Filter\GeohashCell', ['field', 'location']);
        $this->_assertImplemented($filterDSL, 'has_child', 'Elastica\Filter\HasChild', [new Match(), 'type']);
        $this->_assertImplemented($filterDSL, 'has_parent', 'Elastica\Filter\HasParent', [new Match(), 'type']);
        $this->_assertImplemented($filterDSL, 'ids', 'Elastica\Filter\Ids', ['type', []]);
        $this->_assertImplemented($filterDSL, 'indices', 'Elastica\Filter\Indices', [new Exists('field'), []]);
        $this->_assertImplemented($filterDSL, 'limit', 'Elastica\Filter\Limit', [1]);
        $this->_assertImplemented($filterDSL, 'match_all', 'Elastica\Filter\MatchAll', []);
        $this->_assertImplemented($filterDSL, 'missing', 'Elastica\Filter\Missing', ['field']);
        $this->_assertImplemented($filterDSL, 'nested', 'Elastica\Filter\Nested', []);
        $this->_assertImplemented($filterDSL, 'numeric_range', 'Elastica\Filter\NumericRange', []);
        $this->_assertImplemented($filterDSL, 'prefix', 'Elastica\Filter\Prefix', ['field', 'prefix']);
        $this->_assertImplemented($filterDSL, 'query', 'Elastica\Filter\Query', [new Match()]);
        $this->_assertImplemented($filterDSL, 'range', 'Elastica\Filter\Range', ['field', []]);
        $this->_assertImplemented($filterDSL, 'regexp', 'Elastica\Filter\Regexp', ['field', 'regex']);
        $this->_assertImplemented($filterDSL, 'script', 'Elastica\Filter\Script', ['script']);
        $this->_assertImplemented($filterDSL, 'term', 'Elastica\Filter\Term', []);
        $this->_assertImplemented($filterDSL, 'terms', 'Elastica\Filter\Terms', ['field', []]);
        $this->_assertImplemented($filterDSL, 'type', 'Elastica\Filter\Type', ['type']);
        $this->showDeprecated();
    }
}
