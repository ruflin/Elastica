<?php

namespace Elastica\Test\QueryBuilder\DSL;

use Elastica\Filter\Exists;
use Elastica\Query\Match;
use Elastica\QueryBuilder\DSL;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array (method name => arguments)
     */
    private $queries = array(
        'multi_match' => array(),
        'bool' => array(),
        'boosting' => array(),
        'common_terms' => array('field', 'query', .001),
        'custom_filters_score' => array(),
        'custom_score' => array(),
        'custom_boost_factor' => array(),
        'constant_score' => array(),
        'dis_max' => array(),
        'field' => array(),
        'fuzzy_like_this' => array(),
        'fuzzy_like_this_field' => array(),
        'function_score' => array(),
        'fuzzy' => array(),
        'geo_shape' => array(),
        'ids' => array('type', array()),
        'indices' => array(),
        'match_all' => array(),
        'more_like_this' => array(),
        'more_like_this_field' => array(),
        'nested' => array(),
        'prefix' => array(),
        'query_string' => array(),
        'simple_query_string' => array('query'),
        'range' => array('field', array()),
        'regexp' => array('field', 'value', 1.0),
        'span_first' => array(),
        'span_multi_term' => array(),
        'span_near' => array(),
        'span_not' => array(),
        'span_or' => array(),
        'span_term' => array(),
        'term' => array(),
        'terms' => array('field', array()),
        'wildcard' => array(),
        'text' => array(),
        'minimum_should_match' => array(),
        'template' => array(),
    );

    public function __construct()
    {
        $this->queries['filtered'] = array(new Match(), new Exists('field'));
        $this->queries['has_child'] = array(new Match());
        $this->queries['has_parent'] = array(new Match(), 'type');
        $this->queries['top_children'] = array(new Match(), 'type');
    }

    public function testType()
    {
        $queryDSL = new DSL\Query();

        $this->assertInstanceOf('Elastica\QueryBuilder\DSL', $queryDSL);
        $this->assertEquals(DSL::TYPE_QUERY, $queryDSL->getType());
    }

    public function testQueries()
    {
        $queryDSL = new DSL\Query();

        foreach ($this->queries as $methodName => $arguments) {
            $this->assertTrue(
                method_exists($queryDSL, $methodName),
                'method for query "'.$methodName.'" not found'
            );

            try {
                $return = call_user_func_array(array($queryDSL, $methodName), $arguments);
                $this->assertInstanceOf('Elastica\Query\AbstractQuery', $return);
            } catch (\Exception $exception) {
                $this->assertInstanceOf(
                    'Elastica\Exception\NotImplementedException',
                    $exception,
                    'breaking change in query "'.$methodName.'" found: '.$exception->getMessage()
                );
            }
        }
    }

    public function testMatch()
    {
        $queryDSL = new DSL\Query();

        $shortMatch = $queryDSL->match('field', 'match');
        $this->assertEquals($shortMatch->getParam('field'), array(
            'field' => 'match',
        ));

        $this->assertInstanceOf('Elastica\Query\Match', $queryDSL->match());
    }
}
