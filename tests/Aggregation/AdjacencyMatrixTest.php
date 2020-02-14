<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\AdjacencyMatrix;
use Elastica\Aggregation\Avg;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\Term;
use Elastica\Query\Terms;

/**
 * @internal
 */
class AdjacencyMatrixTest extends BaseAggregationTest
{
    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $expected = [
            'adjacency_matrix' => [
                'filters' => [
                    '' => [
                        'term' => ['color' => ''],
                    ],
                    '0' => [
                        'term' => ['color' => '0'],
                    ],
                    'blue' => [
                        'term' => ['color' => 'blue'],
                    ],
                    'red' => [
                        'term' => ['color' => 'red'],
                    ],
                ],
            ],
            'aggs' => [
                'avg_price' => ['avg' => ['field' => 'price']],
            ],
        ];

        $agg = new AdjacencyMatrix('by_color');

        $agg->addFilter(new Term(['color' => '']), '');
        $agg->addFilter(new Term(['color' => '0']), '0');
        $agg->addFilter(new Term(['color' => 'blue']), 'blue');
        $agg->addFilter(new Term(['color' => 'red']), 'red');

        $avg = new Avg('avg_price');
        $avg->setField('price');
        $agg->addAggregation($avg);

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @group unit
     */
    public function testToArraySeparator(): void
    {
        $expected = [
            'adjacency_matrix' => [
                'filters' => [
                    'blue' => [
                        'term' => ['color' => 'blue'],
                    ],
                ],
                'separator' => '|',
            ],
        ];

        $agg = new AdjacencyMatrix('by_color');

        $agg->addFilter(new Term(['color' => 'blue']), 'blue');

        $agg->setSeparator('|');

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @group functional
     */
    public function testAdjacencyMatrixAggregation(): void
    {
        $agg = new AdjacencyMatrix('interactions');
        $agg->addFilter(new Terms('accounts', ['hillary', 'sidney']), 'grpA');
        $agg->addFilter(new Terms('accounts', ['donald', 'mitt']), 'grpB');
        $agg->addFilter(new Terms('accounts', ['vladimir', 'nigel']), 'grpC');

        $agg->setSeparator('|');

        $query = new Query();
        $query->addAggregation($agg);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('interactions');

        $expected = [
            [
                'key' => 'grpA',
                'doc_count' => 2,
            ],
            [
                'key' => 'grpA|grpB',
                'doc_count' => 1,
            ],
            [
                'key' => 'grpB',
                'doc_count' => 2,
            ],
            [
                'key' => 'grpB|grpC',
                'doc_count' => 1,
            ],
            [
                'key' => 'grpC',
                'doc_count' => 1,
            ],
        ];

        $this->assertEquals($expected, $results['buckets']);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document(1, ['accounts' => ['hillary', 'sidney']]),
            new Document(2, ['accounts' => ['hillary', 'donald']]),
            new Document(3, ['accounts' => ['vladimir', 'donald']]),
        ]);

        $index->refresh();

        return $index;
    }
}
