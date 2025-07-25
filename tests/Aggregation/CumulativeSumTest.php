<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\CumulativeSum;
use Elastica\Aggregation\DateHistogram;
use Elastica\Aggregation\Sum;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class CumulativeSumTest extends BaseAggregationTestCase
{
    #[Group('functional')]
    public function testCumulativeSumAggregation(): void
    {
        $query = Query::create([])
            ->addAggregation(
                (new DateHistogram('sales_per_month', 'date', 'month'))
                    ->addAggregation(
                        (new Sum('sales'))
                            ->setField('price')
                    )
                    ->addAggregation(
                        new CumulativeSum('cumulative_sales', 'sales')
                    )
            )
        ;

        $results = $this->getIndexForTest()->search($query)->getAggregations();

        $this->assertEquals(15, $results['sales_per_month']['buckets'][0]['sales']['value']);
        $this->assertEquals(15, $results['sales_per_month']['buckets'][0]['cumulative_sales']['value']);
        $this->assertEquals(22, $results['sales_per_month']['buckets'][1]['sales']['value']);
        $this->assertEquals(37, $results['sales_per_month']['buckets'][1]['cumulative_sales']['value']);
        $this->assertEquals(21, $results['sales_per_month']['buckets'][2]['sales']['value']);
        $this->assertEquals(58, $results['sales_per_month']['buckets'][2]['cumulative_sales']['value']);
    }

    #[Group('unit')]
    public function testConstructThroughSetters(): void
    {
        $aggregation = (new CumulativeSum('cumulative_sum', 'sales'))
            ->setFormat('test_format')
        ;

        $expected = [
            'cumulative_sum' => [
                'buckets_path' => 'sales',
                'format' => 'test_format',
            ],
        ];

        $this->assertSame($expected, $aggregation->toArray());
    }

    private function getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            Document::create(['date' => '2021-01-03', 'price' => 3]),
            Document::create(['date' => '2021-01-05', 'price' => 5]),
            Document::create(['date' => '2021-01-07', 'price' => 7]),
            Document::create(['date' => '2021-02-10', 'price' => 10]),
            Document::create(['date' => '2021-02-12', 'price' => 12]),
            Document::create(['date' => '2021-03-21', 'price' => 21]),
        ], ['refresh' => 'true']);

        return $index;
    }
}
