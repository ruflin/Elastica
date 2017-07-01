<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Avg;
use Elastica\Aggregation\GlobalAggregation;

class GlobalAggregationTest extends BaseAggregationTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $expected = [
            'global' => new \stdClass(),
            'aggs' => [
                'avg_price' => ['avg' => ['field' => 'price']],
            ],
        ];

        $agg = new GlobalAggregation('all_products');
        $avg = new Avg('avg_price');
        $avg->setField('price');
        $agg->addAggregation($avg);
        $this->assertEquals($expected, $agg->toArray());
    }
}
