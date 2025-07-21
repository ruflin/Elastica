<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\DiversifiedSampler;
use Elastica\Aggregation\Sum;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class DiversifiedSamplerTest extends BaseAggregationTestCase
{
    #[Group('unit')]
    public function testToArray(): void
    {
        $expected = [
            'diversified_sampler' => [
                'field' => 'color',
                'shard_size' => 1,
                'max_docs_per_value' => 2,
                'execution_hint' => 'map',
            ],
            'aggs' => [
                'price_sum' => [
                    'sum' => [
                        'field' => 'price',
                    ],
                ],
            ],
        ];

        $agg = new DiversifiedSampler('price_diversified_sampler');
        $agg->setField('color');

        $agg->setShardSize(1);
        $agg->setMaxDocsPerValue(2);
        $agg->setExecutionHint('map');

        $childAgg = new Sum('price_sum');
        $childAgg->setField('price');

        $agg->addAggregation($childAgg);

        $this->assertEquals($expected, $agg->toArray());
    }

    #[DataProvider('shardSizeAndMaxDocPerValueProvider')]
    #[Group('functional')]
    public function testSamplerAggregation(int $shardSize, int $maxDocPerValue, int $docCount): void
    {
        $agg = new DiversifiedSampler('price_diversified_sampler');
        $agg->setField('color');

        $agg->setShardSize($shardSize);
        $agg->setMaxDocsPerValue($maxDocPerValue);

        $childAgg = new Sum('price_sum');
        $childAgg->setField('price');

        $agg->addAggregation($childAgg);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('price_diversified_sampler');

        $this->assertEquals($docCount, $results['doc_count']);
    }

    public static function shardSizeAndMaxDocPerValueProvider(): array
    {
        return [
            [1, 1, 2],
            [2, 1, 4],
            [3, 1, 5],
            [4, 1, 5],
            [1, 2, 2],
            [2, 2, 4],
            [3, 2, 6],
            [4, 2, 8],
            [5, 2, 9],
            [6, 2, 9],
        ];
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex(null, true, 2);

        $mapping = new Mapping([
            'price' => ['type' => 'integer'],
            'color' => ['type' => 'keyword'],
        ]);
        $index->setMapping($mapping);

        $routing1 = 'first_routing';
        $routing2 = 'second_routing';

        $index->addDocuments([
            (new Document('1', ['price' => 5, 'color' => 'blue']))->setRouting($routing1),
            (new Document('2', ['price' => 8, 'color' => 'blue']))->setRouting($routing1),
            (new Document('3', ['price' => 1, 'color' => 'blue']))->setRouting($routing1),
            (new Document('4', ['price' => 3, 'color' => 'red']))->setRouting($routing1),
            (new Document('5', ['price' => 1.5, 'color' => 'red']))->setRouting($routing1),
            (new Document('6', ['price' => 2, 'color' => 'green']))->setRouting($routing1),
            (new Document('7', ['price' => 5, 'color' => 'blue']))->setRouting($routing2),
            (new Document('8', ['price' => 8, 'color' => 'blue']))->setRouting($routing2),
            (new Document('9', ['price' => 1, 'color' => 'red']))->setRouting($routing2),
            (new Document('10', ['price' => 3, 'color' => 'red']))->setRouting($routing2),
        ]);

        $index->refresh();

        return $index;
    }
}
