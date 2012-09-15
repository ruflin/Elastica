<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_BuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Elastica_Query_Builder
     */
    private $builder;

    public function setUp()
    {
        $this->builder = new Elastica_Query_Builder();
    }

    public function tearDown()
    {
        $this->builder = null;
    }

    /**
     * @covers Elastica_Query_Builder::factory
     * @covers Elastica_Query_Builder::__construct
     */
    public function testFactory()
    {
        $this->assertInstanceOf(
            'Elastica_Query_Builder',
            Elastica_Query_Builder::factory('some string')
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
            array('minimumShouldMatch', 21, '{"minimum_number_should_match":"21"}'),
            array('phraseSlop', 6, '{"phrase_slop":"6"}'),
            array('size', 7, '{"size":"7"}'),
            array('tieBreakerMultiplier', 7, '{"tie_breaker_multiplier":"7"}'),
            array('tieBreaker', 7, '{"tie_breaker_multiplier":"7"}'),
            array('matchAll', 1.1, '{"match_all":{"boost":"1.1"}}'),
            array('fields', array("age", "sex", "location"), '{"fields":["age","sex","location"]}'),
        );
    }

    /**
     * @dataProvider getQueryData
     * @covers Elastica_Query_Builder::__toString
     * @covers Elastica_Query_Builder::allowLeadingWildcard
     * @covers Elastica_Query_Builder::analyzeWildcard
     * @covers Elastica_Query_Builder::analyzer
     * @covers Elastica_Query_Builder::autoGeneratePhraseQueries
     * @covers Elastica_Query_Builder::boost
     * @covers Elastica_Query_Builder::defaultField
     * @covers Elastica_Query_Builder::defaultOperator
     * @covers Elastica_Query_Builder::enablePositionIncrements
     * @covers Elastica_Query_Builder::explain
     * @covers Elastica_Query_Builder::from
     * @covers Elastica_Query_Builder::fuzzyMinSim
     * @covers Elastica_Query_Builder::fuzzyPrefixLength
     * @covers Elastica_Query_Builder::gt
     * @covers Elastica_Query_Builder::gte
     * @covers Elastica_Query_Builder::lowercaseExpandedTerms
     * @covers Elastica_Query_Builder::lt
     * @covers Elastica_Query_Builder::lte
     * @covers Elastica_Query_Builder::minimumNumberShouldMatch
     * @covers Elastica_Query_Builder::minimumShouldMatch
     * @covers Elastica_Query_Builder::phraseSlop
     * @covers Elastica_Query_Builder::size
     * @covers Elastica_Query_Builder::tieBreakerMultiplier
     * @covers Elastica_Query_Builder::tieBreaker
     * @covers Elastica_Query_Builder::matchAll
     * @covers Elastica_Query_Builder::fields
     */
    public function testAllowLeadingWildcard($method, $argument, $result)
    {
        $this->assertSame($this->builder, $this->builder->$method($argument));
        $this->assertSame($result, (string) $this->builder);
    }

    public function getQueryTypes()
    {
        return array(
            array('bool', 'bool'),
            array('constantScore', 'constant_score'),
            array('disMax', 'dis_max'),
            array('facets', 'facets'),
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
     * @dataProvider getQueryTypes
     *
     * @covers Elastica_Query_Builder::fieldClose
     * @covers Elastica_Query_Builder::close
     *
     * @covers Elastica_Query_Builder::bool
     * @covers Elastica_Query_Builder::boolClose
     * @covers Elastica_Query_Builder::constantScore
     * @covers Elastica_Query_Builder::constantScoreClose
     * @covers Elastica_Query_Builder::disMax
     * @covers Elastica_Query_Builder::disMaxClose
     * @covers Elastica_Query_Builder::facets
     * @covers Elastica_Query_Builder::facetsClose
     * @covers Elastica_Query_Builder::filter
     * @covers Elastica_Query_Builder::filterClose
     * @covers Elastica_Query_Builder::filteredQuery
     * @covers Elastica_Query_Builder::filteredQueryClose
     * @covers Elastica_Query_Builder::must
     * @covers Elastica_Query_Builder::mustClose
     * @covers Elastica_Query_Builder::mustNot
     * @covers Elastica_Query_Builder::mustNotClose
     * @covers Elastica_Query_Builder::prefix
     * @covers Elastica_Query_Builder::prefixClose
     * @covers Elastica_Query_Builder::query
     * @covers Elastica_Query_Builder::queryClose
     * @covers Elastica_Query_Builder::queryString
     * @covers Elastica_Query_Builder::queryStringClose
     * @covers Elastica_Query_Builder::range
     * @covers Elastica_Query_Builder::rangeClose
     * @covers Elastica_Query_Builder::should
     * @covers Elastica_Query_Builder::shouldClose
     * @covers Elastica_Query_Builder::sort
     * @covers Elastica_Query_Builder::sortClose
     * @covers Elastica_Query_Builder::term
     * @covers Elastica_Query_Builder::termClose
     * @covers Elastica_Query_Builder::textPhrase
     * @covers Elastica_Query_Builder::textPhraseClose
     * @covers Elastica_Query_Builder::wildcard
     * @covers Elastica_Query_Builder::wildcardClose
     */
    public function testQueryTypes($method, $queryType)
    {
        $this->assertSame($this->builder, $this->builder->$method()); // open
        $this->assertSame($this->builder, $this->builder->{$method."Close"}()); // close
        $this->assertSame('{"' . $queryType . '":{}}', (string) $this->builder);
    }

    /**
     * @covers Elastica_Query_Builder::fieldOpen
     * @covers Elastica_Query_Builder::fieldClose
     * @covers Elastica_Query_Builder::open
     * @covers Elastica_Query_Builder::close
     */
    public function testFieldOpenAndClose()
    {
        $this->assertSame($this->builder, $this->builder->fieldOpen('someField'));
        $this->assertSame($this->builder, $this->builder->fieldClose());
        $this->assertSame('{"someField":{}}', (string) $this->builder);
    }

    /**
     * @covers Elastica_Query_Builder::sortField
     */
    public function testSortField()
    {
        $this->assertSame($this->builder, $this->builder->sortField('name', true));
        $this->assertSame('{"sort":{"name":{"reverse":"true"}}}', (string) $this->builder);
    }

    /**
     * @covers Elastica_Query_Builder::sortFields
     */
    public function testSortFields()
    {
        $this->assertSame($this->builder, $this->builder->sortFields(array('field1' => 'asc', 'field2' => 'desc', 'field3' => 'asc')));
        $this->assertSame('{"sort":[{"field1":"asc"},{"field2":"desc"},{"field3":"asc"}]}', (string) $this->builder);
    }

    /**
     * @covers Elastica_Query_Builder::queries
     */
    public function testQueries()
    {
        $queries = array();

        $b1 = clone $this->builder;
        $b2 = clone $this->builder;

        $queries[] = $b1->term()->field('age', 34)->termClose();
        $queries[] = $b2->term()->field('name', 'christer')->termClose();

        $this->assertSame($this->builder, $this->builder->queries($queries));
        $this->assertSame('{"queries":[{"term":{"age":"34"}},{"term":{"name":"christer"}}]}', (string) $this->builder);
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
     * @dataProvider getFieldData
     * @covers Elastica_Query_Builder::field
     */
    public function testField($name, $value, $result)
    {
        $this->assertSame($this->builder, $this->builder->field($name, $value));
        $this->assertSame($result, (string) $this->builder);
    }

    /**
     * @expectedException Elastica_Exception_Invalid
     * @covers Elastica_Query_Builder::toArray
     */
    public function testToArrayWithInvalidData()
    {
        $this->builder->open('foo');
        $this->builder->toArray();
    }

    /**
     * @covers Elastica_Query_Builder::toArray
     */
    public function testToArray()
    {
        $this->builder->query()->term()->field('category.id', array(1, 2, 3))->termClose()->queryClose();
        $this->assertEquals(array(
            'query' => array(
                'term' => array(
                    'category.id' => array(1, 2, 3)
                )
            )
        ), $this->builder->toArray());
    }
}
