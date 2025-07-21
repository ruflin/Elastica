<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\BucketSort;
use Elastica\Aggregation\Sum;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class BucketSortTest extends BaseAggregationTestCase
{
    #[Group('functional')]
    public function testBucketSortAggregation(): void
    {
        $bucketSortAggregation = new BucketSort('sort_by_bucket');
        $bucketSortAggregation->addSort('sum_metric_a', 'desc');
        $agg = $this->getAggregation();
        $agg->addAggregation($bucketSortAggregation);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregations();

        $this->assertSame('bar', $results['terms']['buckets'][0]['key']);

        $bucketSortAggregation = new BucketSort('sort_by_bucket');
        $bucketSortAggregation->addSort('sum_metric_a', 'asc');
        $agg = $this->getAggregation();
        $agg->addAggregation($bucketSortAggregation);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregations();

        $this->assertSame('foo', $results['terms']['buckets'][0]['key']);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'field_a' => ['type' => 'keyword'],
            'metric_a' => ['type' => 'integer'],
        ]));

        $index->addDocuments([
            new Document('1', ['field_a' => 'foo', 'metric_a' => 1]),
            new Document('2', ['field_a' => 'foo', 'metric_a' => 1]),
            new Document('3', ['field_a' => 'foo', 'metric_a' => 1]),
            new Document('4', ['field_a' => 'bar', 'metric_a' => 10]),
            new Document('5', ['field_a' => 'bar', 'metric_a' => 10]),
            new Document('6', ['field_a' => 'bar', 'metric_a' => 10]),
        ]);

        $index->refresh();

        return $index;
    }

    private function getAggregation(): Terms
    {
        $agg = new Terms('terms');
        $agg->setField('field_a');

        $subAgg = new Sum('sum_metric_a');
        $subAgg->setField('metric_a');
        $agg->addAggregation($subAgg);

        $subAgg = new Sum('sum_metric_b');
        $subAgg->setField('metric_b');
        $agg->addAggregation($subAgg);

        return $agg;
    }
}
