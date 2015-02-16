<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\ScriptedMetric;
use Elastica\Document;
use Elastica\Query;
use Elastica\Type\Mapping;

class ScriptedMetricTest extends BaseAggregationTest
{
    protected function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex();
        $mapping = new Mapping();
        $mapping->setProperties(array(
            "start" => array("type" => "long"),
            "end"   => array("type" => "long"),
        ));
        $type = $this->_index->getType("test");
        $type->setMapping($mapping);
        $docs = array(
            new Document("1", array("start" => 100, "end" => 200)),
            new Document("2", array("start" => 200, "end" => 250)),
            new Document("3", array("start" => 300, "end" => 450)),
        );
        $type->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testScriptedMetricAggregation()
    {
        $agg = new ScriptedMetric(
            "scripted",
            "_agg['durations'] = [:]",
            "key = doc['start'].value+ \":\"+ doc['end'].value; _agg.durations[key] = doc['end'].value - doc['start'].value;",
            "values = []; for (item in _agg.durations) { values.add(item.value) }; return values"
        );

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_index->search($query)->getAggregation("scripted");

        $this->assertEquals(array(100, 50, 150), $results['value'][0]);
    }
}
