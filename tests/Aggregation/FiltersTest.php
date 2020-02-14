<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Avg;
use Elastica\Aggregation\Filters;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\Term;

/**
 * @internal
 */
class FiltersTest extends BaseAggregationTest
{
    /**
     * @group unit
     */
    public function testToArrayUsingNamedFilters(): void
    {
        $expected = [
            'filters' => [
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

        $agg = new Filters('by_color');

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
    public function testMixNamedAndAnonymousFilters(): void
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);
        $this->expectExceptionMessage('Mix named and anonymous keys are not allowed');

        $agg = new Filters('by_color');
        $agg->addFilter(new Term(['color' => '0']), '0');
        $agg->addFilter(new Term(['color' => '0']));
    }

    /**
     * @group unit
     */
    public function testMixAnonymousAndNamedFilters(): void
    {
        $this->expectException(\Elastica\Exception\InvalidException::class);
        $this->expectExceptionMessage('Mix named and anonymous keys are not allowed');

        $agg = new Filters('by_color');

        $agg->addFilter(new Term(['color' => '0']));
        $agg->addFilter(new Term(['color' => '0']), '0');
    }

    /**
     * @group unit
     */
    public function testToArrayUsingAnonymousFilters(): void
    {
        $expected = [
            'filters' => [
                'filters' => [
                    [
                        'term' => ['color' => 'blue'],
                    ],
                    [
                        'term' => ['color' => 'red'],
                    ],
                ],
            ],
            'aggs' => [
                'avg_price' => ['avg' => ['field' => 'price']],
            ],
        ];

        $agg = new Filters('by_color');

        $agg->addFilter(new Term(['color' => 'blue']));
        $agg->addFilter(new Term(['color' => 'red']));

        $avg = new Avg('avg_price');
        $avg->setField('price');
        $agg->addAggregation($avg);

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @group unit
     */
    public function testToArrayUsingOtherBucket(): void
    {
        $expected = [
            'filters' => [
                'filters' => [
                    [
                        'term' => ['color' => 'blue'],
                    ],
                ],
                'other_bucket' => true,
                'other_bucket_key' => 'other',
            ],
        ];

        $agg = new Filters('by_color');

        $agg->addFilter(new Term(['color' => 'blue']));

        $agg->setOtherBucket(true);
        $agg->setOtherBucketKey('other');

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @group functional
     */
    public function testFilterAggregation(): void
    {
        $agg = new Filters('by_color');
        $agg->addFilter(new Term(['color' => 'blue']), 'blue');
        $agg->addFilter(new Term(['color' => 'red']), 'red');

        $avg = new Avg('avg_price');
        $avg->setField('price');
        $agg->addAggregation($avg);

        $query = new Query();
        $query->addAggregation($agg);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('by_color');

        $resultsForBlue = $results['buckets']['blue'];
        $resultsForRed = $results['buckets']['red'];

        $this->assertEquals(2, $resultsForBlue['doc_count']);
        $this->assertEquals(1, $resultsForRed['doc_count']);

        $this->assertEquals((5 + 8) / 2, $resultsForBlue['avg_price']['value']);
        $this->assertEquals(1, $resultsForRed['avg_price']['value']);
    }

    /**
     * @group functional
     */
    public function testSetOtherBucket(): void
    {
        $agg = new Filters('by_color');
        $agg->addFilter(new Term(['color' => 'red']), 'red');
        $agg->setOtherBucket(true);

        $avg = new Avg('avg_price');
        $avg->setField('price');
        $agg->addAggregation($avg);

        $query = new Query();
        $query->addAggregation($agg);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('by_color');

        $resultsForRed = $results['buckets']['red'];
        $resultsForOtherBucket = $results['buckets']['_other_'];

        $this->assertEquals(1, $resultsForRed['doc_count']);
        $this->assertEquals(3, $resultsForOtherBucket['doc_count']);

        $this->assertEquals(1, $resultsForRed['avg_price']['value']);
        $this->assertEquals((5 + 8 + 3) / 3, $resultsForOtherBucket['avg_price']['value']);
    }

    /**
     * @group functional
     */
    public function testSetOtherBucketKey(): void
    {
        $agg = new Filters('by_color');
        $agg->addFilter(new Term(['color' => 'red']), 'red');
        $agg->setOtherBucket(true);
        $agg->setOtherBucketKey('other_colors');

        $avg = new Avg('avg_price');
        $avg->setField('price');
        $agg->addAggregation($avg);

        $query = new Query();
        $query->addAggregation($agg);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('by_color');

        $resultsForRed = $results['buckets']['red'];
        $resultsForOtherBucket = $results['buckets']['other_colors'];

        $this->assertEquals(1, $resultsForRed['doc_count']);
        $this->assertEquals(3, $resultsForOtherBucket['doc_count']);

        $this->assertEquals(1, $resultsForRed['avg_price']['value']);
        $this->assertEquals((5 + 8 + 3) / 3, $resultsForOtherBucket['avg_price']['value']);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex('filter');

        $index->addDocuments([
            new Document(1, ['price' => 5, 'color' => 'blue']),
            new Document(2, ['price' => 8, 'color' => 'blue']),
            new Document(3, ['price' => 1, 'color' => 'red']),
            new Document(4, ['price' => 3, 'color' => 'green']),
        ]);

        $index->refresh();

        return $index;
    }
}
