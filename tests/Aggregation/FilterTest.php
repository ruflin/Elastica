<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Avg;
use Elastica\Aggregation\Filter;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\Range;
use Elastica\Query\Term;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class FilterTest extends BaseAggregationTestCase
{
    #[Group('unit')]
    public function testToArray(): void
    {
        $expected = [
            'filter' => ['range' => ['stock' => ['gt' => 0]]],
            'aggs' => [
                'avg_price' => ['avg' => ['field' => 'price']],
            ],
        ];

        $agg = new Filter('in_stock_products');
        $agg->setFilter(new Range('stock', ['gt' => 0]));
        $avg = new Avg('avg_price');
        $avg->setField('price');
        $agg->addAggregation($avg);

        $this->assertEquals($expected, $agg->toArray());
    }

    #[Group('functional')]
    public function testFilterAggregation(): void
    {
        $agg = new Filter('filter');
        $agg->setFilter(new Term(['color' => 'blue']));
        $avg = new Avg('price');
        $avg->setField('price');
        $agg->addAggregation($avg);

        $query = new Query();
        $query->addAggregation($agg);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('filter');
        $results = $results['price']['value'];

        $this->assertEquals((5 + 8) / 2.0, $results);
    }

    #[Group('functional')]
    public function testFilterNoSubAggregation(): void
    {
        $agg = new Avg('price');
        $agg->setField('price');

        $query = new Query();
        $query->addAggregation($agg);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('price');
        $results = $results['value'];

        $this->assertEquals((5 + 8 + 1 + 3) / 4.0, $results);
    }

    #[Group('unit')]
    public function testConstruct(): void
    {
        $agg = new Filter('foo', new Term(['color' => 'blue']));

        $expected = [
            'filter' => [
                'term' => [
                    'color' => 'blue',
                ],
            ],
        ];

        $this->assertEquals($expected, $agg->toArray());
    }

    #[Group('unit')]
    public function testConstructWithoutFilter(): void
    {
        $agg = new Filter('foo');
        $this->assertEquals('foo', $agg->getName());
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['price' => 5, 'color' => 'blue']),
            new Document('2', ['price' => 8, 'color' => 'blue']),
            new Document('3', ['price' => 1, 'color' => 'red']),
            new Document('4', ['price' => 3, 'color' => 'green']),
        ]);

        $index->refresh();

        return $index;
    }
}
