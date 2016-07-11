<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Avg;
use Elastica\Aggregation\Filters;
use Elastica\Document;
use Elastica\Filter\Exists;
use Elastica\Filter\Term;
use Elastica\Query;

class FiltersTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex('filter');

        $index->getType('test')->addDocuments([
            new Document(1, ['price' => 5, 'color' => 'blue']),
            new Document(2, ['price' => 8, 'color' => 'blue']),
            new Document(3, ['price' => 1, 'color' => 'red']),
            new Document(4, ['price' => 3, 'color' => 'green']),
        ]);

        $index->refresh();

        return $index;
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testAddFilterInvalid()
    {
        $filters = new Filters('test');
        $filters->addFilter($this);
    }

    /**
     * @group unit
     */
    public function testSetFilterWithLegacyFilterDeprecated()
    {
        $this->hideDeprecated();
        $existsFilter = new Exists('test');
        $this->showDeprecated();

        $agg = new Filters('test');

        $errorsCollector = $this->startCollectErrors();
        $agg->addFilter($existsFilter);
        $this->finishCollectErrors();

        $errorsCollector->assertOnlyDeprecatedErrors(
            [
                'Deprecated: Elastica\Aggregation\Filters\addFilter() passing filter as AbstractFilter is deprecated. Pass instance of AbstractQuery instead.',
            ]
        );
    }

    /**
     * @group unit
     */
    public function testToArrayUsingNamedFilters()
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

        $agg->addFilter(new Query\Term(['color' => '']), '');
        $agg->addFilter(new Query\Term(['color' => '0']), '0');
        $agg->addFilter(new Query\Term(['color' => 'blue']), 'blue');
        $agg->addFilter(new Query\Term(['color' => 'red']), 'red');

        $avg = new Avg('avg_price');
        $avg->setField('price');
        $agg->addAggregation($avg);

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @group unit
     */
    public function testToArrayUsingNamedFiltersWithLegacyFilters()
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

        $this->hideDeprecated();
        $agg->addFilter(new Term(['color' => '']), '');
        $agg->addFilter(new Term(['color' => '0']), '0');
        $agg->addFilter(new Term(['color' => 'blue']), 'blue');
        $agg->addFilter(new Term(['color' => 'red']), 'red');
        $this->showDeprecated();

        $avg = new Avg('avg_price');
        $avg->setField('price');
        $agg->addAggregation($avg);

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     * @expectedExceptionMessage Name must be a string
     */
    public function testWrongName()
    {
        $agg = new Filters('by_color');
        $this->hideDeprecated();
        $agg->addFilter(new Query\Term(['color' => '0']), 0);
        $this->showDeprecated();
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     * @expectedExceptionMessage Name must be a string
     */
    public function testWrongNameWithLegacyFilter()
    {
        $agg = new Filters('by_color');
        $this->hideDeprecated();
        $agg->addFilter(new Term(['color' => '0']), 0);
        $this->showDeprecated();
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     * @expectedExceptionMessage Mix named and anonymous keys are not allowed
     */
    public function testMixNamedAndAnonymousFilters()
    {
        $agg = new Filters('by_color');
        $agg->addFilter(new Query\Term(['color' => '0']), '0');
        $agg->addFilter(new Query\Term(['color' => '0']));
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     * @expectedExceptionMessage Mix named and anonymous keys are not allowed
     */
    public function testMixNamedAndAnonymousFiltersWithLegacyFilters()
    {
        $agg = new Filters('by_color');
        $this->hideDeprecated();
        $agg->addFilter(new Term(['color' => '0']), '0');
        $agg->addFilter(new Term(['color' => '0']));
        $this->showDeprecated();
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     * @expectedExceptionMessage Mix named and anonymous keys are not allowed
     */
    public function testMixAnonymousAndNamedFilters()
    {
        $agg = new Filters('by_color');

        $agg->addFilter(new Query\Term(['color' => '0']));
        $agg->addFilter(new Query\Term(['color' => '0']), '0');
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     * @expectedExceptionMessage Mix named and anonymous keys are not allowed
     */
    public function testMixAnonymousAndNamedFiltersWithLegacyFilters()
    {
        $agg = new Filters('by_color');

        $this->hideDeprecated();
        $agg->addFilter(new Term(['color' => '0']));
        $agg->addFilter(new Term(['color' => '0']), '0');
        $this->showDeprecated();
    }

    /**
     * @group unit
     */
    public function testToArrayUsingAnonymousFilters()
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

        $agg->addFilter(new Query\Term(['color' => 'blue']));
        $agg->addFilter(new Query\Term(['color' => 'red']));

        $avg = new Avg('avg_price');
        $avg->setField('price');
        $agg->addAggregation($avg);

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @group unit
     */
    public function testToArrayUsingAnonymousFiltersWithLegacyFilters()
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

        $this->hideDeprecated();
        $agg->addFilter(new Term(['color' => 'blue']));
        $agg->addFilter(new Term(['color' => 'red']));
        $this->showDeprecated();

        $avg = new Avg('avg_price');
        $avg->setField('price');
        $agg->addAggregation($avg);

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @group functional
     */
    public function testFilterAggregation()
    {
        $agg = new Filters('by_color');
        $agg->addFilter(new Query\Term(['color' => 'blue']), 'blue');
        $agg->addFilter(new Query\Term(['color' => 'red']), 'red');

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
    public function testFilterAggregationWithLegacyFilters()
    {
        $agg = new Filters('by_color');
        $this->hideDeprecated();
        $agg->addFilter(new Term(['color' => 'blue']), 'blue');
        $agg->addFilter(new Term(['color' => 'red']), 'red');
        $this->showDeprecated();

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
}
