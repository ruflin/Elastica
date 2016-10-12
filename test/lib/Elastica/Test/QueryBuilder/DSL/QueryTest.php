<?php
namespace Elastica\Test\QueryBuilder\DSL;

use Elastica\Query\Match;
use Elastica\QueryBuilder\DSL;

class QueryTest extends AbstractDSLTest
{
    /**
     * @group unit
     */
    public function testType()
    {
        $queryDSL = new DSL\Query();

        $this->assertInstanceOf('Elastica\QueryBuilder\DSL', $queryDSL);
        $this->assertEquals(DSL::TYPE_QUERY, $queryDSL->getType());
    }

    /**
     * @group unit
     */
    public function testMatch()
    {
        $queryDSL = new DSL\Query();

        $match = $queryDSL->match('field', 'match');
        $this->assertEquals('match', $match->getParam('field'));
        $this->assertInstanceOf('Elastica\Query\Match', $match);
    }

    /**
     * @group unit
     */
    public function testInterface()
    {
        $queryDSL = new DSL\Query();

        $this->_assertImplemented($queryDSL, 'bool', 'Elastica\Query\BoolQuery', []);
        $this->_assertImplemented($queryDSL, 'boosting', 'Elastica\Query\Boosting', []);
        $this->_assertImplemented($queryDSL, 'common_terms', 'Elastica\Query\Common', ['field', 'query', 0.001]);
        $this->_assertImplemented($queryDSL, 'dis_max', 'Elastica\Query\DisMax', []);
        $this->_assertImplemented($queryDSL, 'function_score', 'Elastica\Query\FunctionScore', []);
        $this->_assertImplemented($queryDSL, 'fuzzy', 'Elastica\Query\Fuzzy', ['field', 'type']);
        $this->_assertImplemented($queryDSL, 'has_child', 'Elastica\Query\HasChild', [new Match()]);
        $this->_assertImplemented($queryDSL, 'has_parent', 'Elastica\Query\HasParent', [new Match(), 'type']);
        $this->_assertImplemented($queryDSL, 'ids', 'Elastica\Query\Ids', ['type', []]);
        $this->_assertImplemented($queryDSL, 'match', 'Elastica\Query\Match', ['field', 'values']);
        $this->_assertImplemented($queryDSL, 'match_all', 'Elastica\Query\MatchAll', []);
        $this->_assertImplemented($queryDSL, 'more_like_this', 'Elastica\Query\MoreLikeThis', []);
        $this->_assertImplemented($queryDSL, 'multi_match', 'Elastica\Query\MultiMatch', []);
        $this->_assertImplemented($queryDSL, 'nested', 'Elastica\Query\Nested', []);
        $this->_assertImplemented($queryDSL, 'prefix', 'Elastica\Query\Prefix', []);
        $this->_assertImplemented($queryDSL, 'query_string', 'Elastica\Query\QueryString', []);
        $this->_assertImplemented($queryDSL, 'range', 'Elastica\Query\Range', ['field', []]);
        $this->_assertImplemented($queryDSL, 'regexp', 'Elastica\Query\Regexp', ['field', 'value', 1.0]);
        $this->_assertImplemented($queryDSL, 'simple_query_string', 'Elastica\Query\SimpleQueryString', ['query']);
        $this->_assertImplemented($queryDSL, 'term', 'Elastica\Query\Term', []);
        $this->_assertImplemented($queryDSL, 'terms', 'Elastica\Query\Terms', ['field', []]);
        $this->_assertImplemented($queryDSL, 'top_children', 'Elastica\Query\TopChildren', [new Match(), 'type']);
        $this->_assertImplemented($queryDSL, 'wildcard', 'Elastica\Query\Wildcard', []);
        $this->_assertImplemented(
            $queryDSL,
            'geo_distance',
            'Elastica\Query\GeoDistance',
            ['key', ['lat' => 1, 'lon' => 0], 'distance']
        );
        $this->_assertImplemented($queryDSL, 'exists', 'Elastica\Query\Exists', ['field']);
        $this->_assertImplemented($queryDSL, 'type', 'Elastica\Query\Type', []);
        $this->_assertImplemented($queryDSL, 'type', 'Elastica\Query\Type', ['type']);

        $this->_assertNotImplemented($queryDSL, 'custom_boost_factor', []);
        $this->_assertNotImplemented($queryDSL, 'custom_filters_score', []);
        $this->_assertNotImplemented($queryDSL, 'custom_score', []);
        $this->_assertNotImplemented($queryDSL, 'field', []);
        $this->_assertNotImplemented($queryDSL, 'geo_shape', []);
        $this->_assertNotImplemented($queryDSL, 'indices', []);
        $this->_assertNotImplemented($queryDSL, 'minimum_should_match', []);
        $this->_assertNotImplemented($queryDSL, 'more_like_this_field', []);
        $this->_assertNotImplemented($queryDSL, 'span_first', []);
        $this->_assertNotImplemented($queryDSL, 'span_multi_term', []);
        $this->_assertNotImplemented($queryDSL, 'span_near', []);
        $this->_assertNotImplemented($queryDSL, 'span_not', []);
        $this->_assertNotImplemented($queryDSL, 'span_or', []);
        $this->_assertNotImplemented($queryDSL, 'span_term', []);
        $this->_assertNotImplemented($queryDSL, 'template', []);
        $this->_assertNotImplemented($queryDSL, 'text', []);
    }
}
