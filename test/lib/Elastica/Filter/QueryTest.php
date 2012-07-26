<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Filter_QueryTest extends PHPUnit_Framework_TestCase
{
    public function testSimple()
    {
        $query = new Elastica_Query_QueryString('foo bar');
        $filter = new Elastica_Filter_Query($query);

        $expected = array(
            'query' => array(
                'query_string' => array(
                    'query' => 'foo bar',
                )
            )
        );

        $this->assertEquals($expected, $filter->toArray());
    }

    public function testExtended()
    {
        $query = new Elastica_Query_QueryString('foo bar');
        $filter = new Elastica_Filter_Query($query);
        $filter->setCached(true);

        $expected = array(
            'fquery' => array(
                'query' => array(
                    'query_string' => array(
                        'query' => 'foo bar',
                    ),
                ),
                '_cache' => true
            )
        );

        $this->assertEquals($expected, $filter->toArray());
    }
}
