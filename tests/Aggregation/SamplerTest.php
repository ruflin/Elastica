<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Sampler;
use Elastica\Aggregation\Sum;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;

/**
 * @internal
 */
class SamplerTest extends BaseAggregationTest
{
    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $expected = [
            'sampler' => [
                'shard_size' => 1,
            ],
            'aggs' => [
                'price_sum' => [
                    'sum' => [
                        'field' => 'price',
                    ],
                ],
            ],
        ];

        $agg = new Sampler('price_sampler');
        $agg->setShardSize(1);

        $childAgg = new Sum('price_sum');
        $childAgg->setField('price');

        $agg->addAggregation($childAgg);

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @dataProvider shardSizeProvider
     * @group functional
     */
    public function testSamplerAggregation(int $shardSize, int $docCount): void
    {
        $agg = new Sampler('price_sampler');
        $agg->setShardSize($shardSize);

        $childAgg = new Sum('price_sum');
        $childAgg->setField('price');

        $agg->addAggregation($childAgg);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('price_sampler');

        $this->assertEquals($docCount, $results['doc_count']);
    }

    public function shardSizeProvider()
    {
        return [
            [1, 2],
            [2, 4],
            [3, 5],
        ];
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex(null, true, 2);

        $routing1 = 'first_routing';
        $routing2 = 'second_routing';

        $index->addDocuments([
            (new Document(1, ['price' => 5]))->setRouting($routing1),
            (new Document(2, ['price' => 8]))->setRouting($routing1),
            (new Document(3, ['price' => 1]))->setRouting($routing1),
            (new Document(4, ['price' => 3]))->setRouting($routing2),
            (new Document(5, ['price' => 1.5]))->setRouting($routing2),
        ]);

        $index->refresh();

        return $index;
    }
}
