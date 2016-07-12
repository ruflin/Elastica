<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\ScriptedMetric;
use Elastica\Document;
use Elastica\Query;
use Elastica\Type\Mapping;

class ScriptedMetricTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $type->setMapping(new Mapping(null, [
            'start' => ['type' => 'long'],
            'end' => ['type' => 'long'],
        ]));

        $type->addDocuments([
            new Document(1, ['start' => 100, 'end' => 200]),
            new Document(2, ['start' => 200, 'end' => 250]),
            new Document(3, ['start' => 300, 'end' => 450]),
        ]);

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testScriptedMetricAggregation()
    {
        $this->_checkScriptInlineSetting();
        $agg = new ScriptedMetric(
            'scripted',
            "_agg['durations'] = [:]",
            "key = doc['start'].value+ \":\"+ doc['end'].value; _agg.durations[key] = doc['end'].value - doc['start'].value;",
            'values = []; for (item in _agg.durations) { values.add(item.value) }; return values'
        );

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('scripted');

        $this->assertEquals([100, 50, 150], $results['value'][0]);
    }
}
