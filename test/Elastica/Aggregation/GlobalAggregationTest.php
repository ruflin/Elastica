<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Avg;
use Elastica\Aggregation\Filter;
use Elastica\Aggregation\GlobalAggregation;
use Elastica\Query;
use Elastica\QueryBuilder;
use http\QueryString;

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
    /**
     * @group unit
     */
    public function testFilterAllAggregation()
    {
        $expected = [
            'global' => new \stdClass(),
            'aggs' => [
                'all' => ['filter' => ['bool' => ['must' => [0 => ['terms' => ['field' => [0 => 'price']]]]]]],
            ],
        ];

        $boolQuery = new Query\BoolQuery();
        $boolQuery->addMust(new Query\Terms('field', ['price']));
        $agg = new GlobalAggregation('products', $boolQuery);

        $this->assertEquals($expected, $agg->toArray());
    }

}
