<?php

declare(strict_types=1);

namespace Elastica\Test\QueryBuilder\DSL;

use Elastica\Query;
use Elastica\Query\MatchQuery;
use Elastica\QueryBuilder\DSL;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('unit')]
class QueryTest extends AbstractDSLTestCase
{
    public function testType(): void
    {
        $queryDSL = new DSL\Query();

        $this->assertEquals(DSL::TYPE_QUERY, $queryDSL->getType());
    }

    public function testMatch(): void
    {
        $match = (new DSL\Query())
            ->match('field', 'value')
        ;

        $this->assertEquals('value', $match->getParam('field'));
    }

    public function testInterface(): void
    {
        $queryDSL = new DSL\Query();

        $this->_assertImplemented($queryDSL, 'bool', Query\BoolQuery::class, []);
        $this->_assertImplemented($queryDSL, 'boosting', Query\Boosting::class, []);
        $this->_assertImplemented($queryDSL, 'dis_max', Query\DisMax::class, []);
        $this->_assertImplemented($queryDSL, 'distance_feature', Query\DistanceFeature::class, ['field', 'now', '7d']);
        $this->_assertImplemented($queryDSL, 'function_score', Query\FunctionScore::class, []);
        $this->_assertImplemented($queryDSL, 'fuzzy', Query\Fuzzy::class, ['field', 'type']);
        $this->_assertImplemented($queryDSL, 'has_child', Query\HasChild::class, [new MatchQuery()]);
        $this->_assertImplemented($queryDSL, 'has_parent', Query\HasParent::class, [new MatchQuery(), 'type']);
        $this->_assertImplemented($queryDSL, 'ids', Query\Ids::class, [[]]);
        $this->_assertImplemented($queryDSL, 'match', MatchQuery::class, ['field', 'values']);
        $this->_assertImplemented($queryDSL, 'match_all', Query\MatchAll::class, []);
        $this->_assertImplemented($queryDSL, 'match_none', Query\MatchNone::class, []);
        $this->_assertImplemented($queryDSL, 'more_like_this', Query\MoreLikeThis::class, []);
        $this->_assertImplemented($queryDSL, 'multi_match', Query\MultiMatch::class, []);
        $this->_assertImplemented($queryDSL, 'nested', Query\Nested::class, []);
        $this->_assertImplemented($queryDSL, 'parent_id', Query\ParentId::class, ['test', '1']);
        $this->_assertImplemented($queryDSL, 'prefix', Query\Prefix::class, []);
        $this->_assertImplemented($queryDSL, 'query_string', Query\QueryString::class, []);
        $this->_assertImplemented($queryDSL, 'range', Query\Range::class, ['field', []]);
        $this->_assertImplemented($queryDSL, 'regexp', Query\Regexp::class, ['field', 'value', 1.0]);
        $this->_assertImplemented($queryDSL, 'simple_query_string', Query\SimpleQueryString::class, ['query']);
        $this->_assertImplemented($queryDSL, 'term', Query\Term::class, []);
        $this->_assertImplemented($queryDSL, 'terms', Query\Terms::class, ['field', []]);
        $this->_assertImplemented($queryDSL, 'terms_set', Query\TermsSet::class, ['field', ['term'], 'match_field']);
        $this->_assertImplemented($queryDSL, 'wildcard', Query\Wildcard::class, ['field', '']);
        $this->_assertImplemented(
            $queryDSL,
            'geo_distance',
            Query\GeoDistance::class,
            ['key', ['lat' => 1, 'lon' => 0], 'distance']
        );
        $this->_assertImplemented($queryDSL, 'exists', Query\Exists::class, ['field']);
        $this->_assertImplemented($queryDSL, 'span_term', Query\SpanTerm::class, []);
        $this->_assertImplemented($queryDSL, 'span_multi_term', Query\SpanMulti::class, []);
        $this->_assertImplemented($queryDSL, 'span_near', Query\SpanNear::class, []);
        $this->_assertImplemented($queryDSL, 'span_or', Query\SpanOr::class, []);
        $this->_assertImplemented($queryDSL, 'span_first', Query\SpanFirst::class, []);
        $this->_assertImplemented($queryDSL, 'span_containing', Query\SpanContaining::class, []);
        $this->_assertImplemented($queryDSL, 'span_within', Query\SpanWithin::class, []);
        $this->_assertImplemented($queryDSL, 'span_not', Query\SpanNot::class, []);
    }
}
