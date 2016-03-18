<?php

namespace Elastica\Test\Query;

use Elastica\Query\Builder;
use Elastica\Test\Base as BaseTest;

class BuilderTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testDeprecated()
    {
        $this->hideDeprecated();
        $reflection = new \ReflectionClass(new Builder());
        $this->showDeprecated();

        $this->assertFileDeprecated($reflection->getFileName(), 'This builder is deprecated and will be removed in further Elastica releases. Use new Elastica\QueryBuilder instead.');
    }

    /**
     * @group unit
     * @covers \Elastica\Query\Builder::factory
     * @covers \Elastica\Query\Builder::__construct
     */
    public function testFactory()
    {
        $this->assertInstanceOf(
            'Elastica\Query\Builder',
            Builder::factory('some string')
        );
    }

    public function getQueryData()
    {
        return array(
            array('allowLeadingWildcard', false, '{"allow_leading_wildcard":"false"}'),
            array('allowLeadingWildcard', true, '{"allow_leading_wildcard":"true"}'),
            array('analyzeWildcard', false, '{"analyze_wildcard":"false"}'),
            array('analyzeWildcard', true, '{"analyze_wildcard":"true"}'),
            array('analyzer', 'someAnalyzer', '{"analyzer":"someAnalyzer"}'),
            array('autoGeneratePhraseQueries', true, '{"auto_generate_phrase_queries":"true"}'),
            array('autoGeneratePhraseQueries', false, '{"auto_generate_phrase_queries":"false"}'),
            array('boost', 2, '{"boost":"2"}'),
            array('boost', 4.2, '{"boost":"4.2"}'),
            array('defaultField', 'fieldName', '{"default_field":"fieldName"}'),
            array('defaultOperator', 'OR', '{"default_operator":"OR"}'),
            array('defaultOperator', 'AND', '{"default_operator":"AND"}'),
            array('enablePositionIncrements', true, '{"enable_position_increments":"true"}'),
            array('enablePositionIncrements', false, '{"enable_position_increments":"false"}'),
            array('explain', true, '{"explain":"true"}'),
            array('explain', false, '{"explain":"false"}'),
            array('from', 42, '{"from":"42"}'),
            array('fuzzyMinSim', 4.2, '{"fuzzy_min_sim":"4.2"}'),
            array('fuzzyPrefixLength', 2, '{"fuzzy_prefix_length":"2"}'),
            array('gt', 10, '{"gt":"10"}'),
            array('gte', 11, '{"gte":"11"}'),
            array('lowercaseExpandedTerms', true, '{"lowercase_expanded_terms":"true"}'),
            array('lt', 10, '{"lt":"10"}'),
            array('lte', 11, '{"lte":"11"}'),
            array('minimumNumberShouldMatch', 21, '{"minimum_number_should_match":"21"}'),
            array('phraseSlop', 6, '{"phrase_slop":"6"}'),
            array('size', 7, '{"size":"7"}'),
            array('tieBreakerMultiplier', 7, '{"tie_breaker_multiplier":"7"}'),
            array('matchAll', 1.1, '{"match_all":{"boost":"1.1"}}'),
            array('fields', array('age', 'sex', 'location'), '{"fields":["age","sex","location"]}'),
        );
    }

    /**
     * @group unit
     * @dataProvider getQueryData
     * @covers \Elastica\Query\Builder::__toString
     * @covers \Elastica\Query\Builder::allowLeadingWildcard
     * @covers \Elastica\Query\Builder::analyzeWildcard
     * @covers \Elastica\Query\Builder::analyzer
     * @covers \Elastica\Query\Builder::autoGeneratePhraseQueries
     * @covers \Elastica\Query\Builder::boost
     * @covers \Elastica\Query\Builder::defaultField
     * @covers \Elastica\Query\Builder::defaultOperator
     * @covers \Elastica\Query\Builder::enablePositionIncrements
     * @covers \Elastica\Query\Builder::explain
     * @covers \Elastica\Query\Builder::from
     * @covers \Elastica\Query\Builder::fuzzyMinSim
     * @covers \Elastica\Query\Builder::fuzzyPrefixLength
     * @covers \Elastica\Query\Builder::gt
     * @covers \Elastica\Query\Builder::gte
     * @covers \Elastica\Query\Builder::lowercaseExpandedTerms
     * @covers \Elastica\Query\Builder::lt
     * @covers \Elastica\Query\Builder::lte
     * @covers \Elastica\Query\Builder::minimumNumberShouldMatch
     * @covers \Elastica\Query\Builder::phraseSlop
     * @covers \Elastica\Query\Builder::size
     * @covers \Elastica\Query\Builder::tieBreakerMultiplier
     * @covers \Elastica\Query\Builder::matchAll
     * @covers \Elastica\Query\Builder::fields
     */
    public function testAllowLeadingWildcard($method, $argument, $result)
    {
        $builder = new Builder();
        $this->assertSame($builder, $builder->$method($argument));
        $this->assertSame($result, (string) $builder);
    }

    public function getQueryTypes()
    {
        return array(
            array('bool', 'bool'),
            array('constantScore', 'constant_score'),
            array('disMax', 'dis_max'),
            array('filter', 'filter'),
            array('filteredQuery', 'filtered'),
            array('must', 'must'),
            array('mustNot', 'must_not'),
            array('prefix', 'prefix'),
            array('query', 'query'),
            array('queryString', 'query_string'),
            array('range', 'range'),
            array('should', 'should'),
            array('sort', 'sort'),
            array('term', 'term'),
            array('textPhrase', 'text_phrase'),
            array('wildcard', 'wildcard'),
        );
    }

    /**
     * @group unit
     * @dataProvider getQueryTypes
     * @covers \Elastica\Query\Builder::fieldClose
     * @covers \Elastica\Query\Builder::close
     * @covers \Elastica\Query\Builder::bool
     * @covers \Elastica\Query\Builder::boolClose
     * @covers \Elastica\Query\Builder::constantScore
     * @covers \Elastica\Query\Builder::constantScoreClose
     * @covers \Elastica\Query\Builder::disMax
     * @covers \Elastica\Query\Builder::disMaxClose
     * @covers \Elastica\Query\Builder::filter
     * @covers \Elastica\Query\Builder::filterClose
     * @covers \Elastica\Query\Builder::filteredQuery
     * @covers \Elastica\Query\Builder::filteredQueryClose
     * @covers \Elastica\Query\Builder::must
     * @covers \Elastica\Query\Builder::mustClose
     * @covers \Elastica\Query\Builder::mustNot
     * @covers \Elastica\Query\Builder::mustNotClose
     * @covers \Elastica\Query\Builder::prefix
     * @covers \Elastica\Query\Builder::prefixClose
     * @covers \Elastica\Query\Builder::query
     * @covers \Elastica\Query\Builder::queryClose
     * @covers \Elastica\Query\Builder::queryString
     * @covers \Elastica\Query\Builder::queryStringClose
     * @covers \Elastica\Query\Builder::range
     * @covers \Elastica\Query\Builder::rangeClose
     * @covers \Elastica\Query\Builder::should
     * @covers \Elastica\Query\Builder::shouldClose
     * @covers \Elastica\Query\Builder::sort
     * @covers \Elastica\Query\Builder::sortClose
     * @covers \Elastica\Query\Builder::term
     * @covers \Elastica\Query\Builder::termClose
     * @covers \Elastica\Query\Builder::textPhrase
     * @covers \Elastica\Query\Builder::textPhraseClose
     * @covers \Elastica\Query\Builder::wildcard
     * @covers \Elastica\Query\Builder::wildcardClose
     */
    public function testQueryTypes($method, $queryType)
    {
        $builder = new Builder();
        $this->assertSame($builder, $builder->$method()); // open
        $this->assertSame($builder, $builder->{$method.'Close'}()); // close
        $this->assertSame('{"'.$queryType.'":{}}', (string) $builder);
    }

    /**
     * @group unit
     * @covers \Elastica\Query\Builder::fieldOpen
     * @covers \Elastica\Query\Builder::fieldClose
     * @covers \Elastica\Query\Builder::open
     * @covers \Elastica\Query\Builder::close
     */
    public function testFieldOpenAndClose()
    {
        $builder = new Builder();
        $this->assertSame($builder, $builder->fieldOpen('someField'));
        $this->assertSame($builder, $builder->fieldClose());
        $this->assertSame('{"someField":{}}', (string) $builder);
    }

    /**
     * @group unit
     * @covers \Elastica\Query\Builder::sortField
     */
    public function testSortField()
    {
        $builder = new Builder();
        $this->assertSame($builder, $builder->sortField('name', true));
        $this->assertSame('{"sort":{"name":{"reverse":"true"}}}', (string) $builder);
    }

    /**
     * @group unit
     * @covers \Elastica\Query\Builder::sortFields
     */
    public function testSortFields()
    {
        $builder = new Builder();
        $this->assertSame($builder, $builder->sortFields(array('field1' => 'asc', 'field2' => 'desc', 'field3' => 'asc')));
        $this->assertSame('{"sort":[{"field1":"asc"},{"field2":"desc"},{"field3":"asc"}]}', (string) $builder);
    }

    /**
     * @group unit
     * @covers \Elastica\Query\Builder::queries
     */
    public function testQueries()
    {
        $queries = array();

        $builder = new Builder();
        $b1 = clone $builder;
        $b2 = clone $builder;

        $queries[] = $b1->term()->field('age', 34)->termClose();
        $queries[] = $b2->term()->field('name', 'christer')->termClose();

        $this->assertSame($builder, $builder->queries($queries));
        $this->assertSame('{"queries":[{"term":{"age":"34"}},{"term":{"name":"christer"}}]}', (string) $builder);
    }

    public function getFieldData()
    {
        return array(
            array('name', 'value', '{"name":"value"}'),
            array('name', true, '{"name":"true"}'),
            array('name', false, '{"name":"false"}'),
            array('name', array(1, 2, 3), '{"name":["1","2","3"]}'),
            array('name', array('foo', 'bar', 'baz'), '{"name":["foo","bar","baz"]}'),
        );
    }

    /**
     * @group unit
     * @dataProvider getFieldData
     * @covers \Elastica\Query\Builder::field
     */
    public function testField($name, $value, $result)
    {
        $builder = new Builder();
        $this->assertSame($builder, $builder->field($name, $value));
        $this->assertSame($result, (string) $builder);
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     * @expectedExceptionMessage The produced query is not a valid json string : "{{}"
     * @covers \Elastica\Query\Builder::toArray
     */
    public function testToArrayWithInvalidData()
    {
        $builder = new Builder();
        $builder->open('foo');
        $builder->toArray();
    }

    /**
     * @group unit
     * @covers \Elastica\Query\Builder::toArray
     */
    public function testToArray()
    {
        $builder = new Builder();
        $builder->query()->term()->field('category.id', array(1, 2, 3))->termClose()->queryClose();
        $expected = array(
            'query' => array(
                'term' => array(
                    'category.id' => array(1, 2, 3),
                ),
            ),
        );
        $this->assertEquals($expected, $builder->toArray());
    }
}
