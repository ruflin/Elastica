<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Sum;
use Elastica\Document;
use Elastica\Query;
use Elastica\Script;

class ScriptTest extends BaseAggregationTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex();
        $docs = array(
            new Document('1', array('price' => 5)),
            new Document('2', array('price' => 8)),
            new Document('3', array('price' => 1)),
            new Document('4', array('price' => 3)),
        );
        $this->_index->getType('test')->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testAggregationScript()
    {
        $agg = new Sum("sum");
        // x = (0..1) is groovy-specific syntax, to see if lang is recognized
        $script = new Script("x = (0..1); return doc['price'].value", null, "groovy");
        $agg->setScript($script);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregation("sum");

        $this->assertEquals(5 + 8 + 1 + 3, $results['value']);
    }

    public function testAggregationScriptAsString()
    {
        $agg = new Sum("sum");
        $agg->setScript("doc['price'].value");

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregation("sum");

        $this->assertEquals(5 + 8 + 1 + 3, $results['value']);
    }

    public function testSetScript()
    {
        $aggregation = "sum";
        $string = "doc['price'].value";
        $params = array(
            'param1' => 'one',
            'param2' => 1,
        );
        $lang = "groovy";

        $agg = new Sum($aggregation);
        $script = new Script($string, $params, $lang);
        $agg->setScript($script);

        $array = $agg->toArray();

        $expected = array(
            $aggregation => array(
                'script' => $string,
                'params' => $params,
                'lang' => $lang,
            ),
        );
        $this->assertEquals($expected, $array);
    }
}
