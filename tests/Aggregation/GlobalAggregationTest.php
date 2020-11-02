<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Avg;
use Elastica\Aggregation\GlobalAggregation;

/**
 * @internal
 */
class GlobalAggregationTest extends BaseAggregationTest
{
    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $expected = [
            'global' => new \ArrayObject(),
            'aggs' => [
                'avg_price' => ['avg' => ['field' => 'price']],
            ],
        ];

        $agg = new GlobalAggregation('all_products');
        $avg = new Avg('avg_price');
        $avg->setField('price');
        $agg->addAggregation($avg);
        $this->assertEquals($expected, $agg->toArray());
        $this->assertSame(
            '{"global":{},"aggs":{"avg_price":{"avg":{"field":"price"}}}}',
            \json_encode($agg->toArray())
        );
    }
}
