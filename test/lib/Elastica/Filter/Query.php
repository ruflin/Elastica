<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Filter_QueryTest extends PHPUnit_Framework_TestCase {

    public function dataProviderSampleQueries() {
        return array(
            array(
                new Elastica_Query_QueryString('foo bar'),
                array(
                    'query' => array(
                        'query_string' => array(
                            'query' => 'foo bar',
                        ),
                    ), 
                ),
            ),
        ); 
    } 
    /**
     * @dataProvider dataProviderSampleQueries
     */ 
    public function testSimple($query, $expected) {
        $filter = new Elastica_Filter_Query($query);

        if(is_string($expected)) {
            $expected = json_decode($expected, true);
        } 
        $this->assertEquals($expected, $filter->toArray());

    }
} 

