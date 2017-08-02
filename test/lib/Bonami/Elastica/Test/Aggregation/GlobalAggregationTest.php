<?php
namespace Elastica\Test\Aggregation;

use Bonami\Elastica\Aggregation\Avg;
use Bonami\Elastica\Aggregation\GlobalAggregation;

class GlobalAggregationTest extends BaseAggregationTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $expected = array(
            'global' => new \stdClass(),
            'aggs' => array(
                'avg_price' => array('avg' => array('field' => 'price')),
            ),
        );

        $agg = new GlobalAggregation('all_products');
        $avg = new Avg('avg_price');
        $avg->setField('price');
        $agg->addAggregation($avg);
        $this->assertEquals($expected, $agg->toArray());
    }
}
