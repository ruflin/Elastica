<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';


class Elastica_Query_CustomScoreTest extends PHPUnit_Framework_TestCase{

	public function setUp() {
	}

	public function tearDown() {
	}

    public function testCustomScoreQuery(){
        $query = new Elastica_Query();

        $customscore_query = new Elastica_Query_CustomScore();
        $customscore_query->setQuery($query);
        $customscore_query->setScript("doc['hits'].value * (param1 + param2)");
        $customscore_query->addParams(array('param1' => 1123, 'param2' => 2001));

        $experted = '{"custom_score":{"query":{"match_all":{}},"script":"doc[\'hits\'].value * (param1 + param2)","params":{"param1":1123,"param2":2001}}}';

        $this->assertEquals($experted, json_encode($customscore_query->toArray()));
    }
}