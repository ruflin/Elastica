<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\ScriptedMetric;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class ScriptedMetricTest extends BaseAggregationTestCase
{
    #[Group('functional')]
    public function testScriptedMetricAggregation(): void
    {
        $agg = new ScriptedMetric(
            'scripted',
            'state.durations = []',
            'state.durations.add(doc.end.value - doc.start.value)',
            'return state.durations',
            'return states'
        );

        $query = new Query();
        $query->setSize(0);
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('scripted');

        $this->assertEquals([100, 50, 150], $results['value'][0]);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->setMapping(new Mapping([
            'start' => ['type' => 'long'],
            'end' => ['type' => 'long'],
        ]));

        $index->addDocuments([
            new Document('1', ['start' => 100, 'end' => 200]),
            new Document('2', ['start' => 200, 'end' => 250]),
            new Document('3', ['start' => 300, 'end' => 450]),
        ]);

        $index->refresh();

        return $index;
    }
}
