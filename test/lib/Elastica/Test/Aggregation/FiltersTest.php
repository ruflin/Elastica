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

        $index->getType('test')->addDocuments(array(
            new Document(1, array('price' => 5, 'color' => 'blue')),
            new Document(2, array('price' => 8, 'color' => 'blue')),
            new Document(3, array('price' => 1, 'color' => 'red')),
            new Document(4, array('price' => 3, 'color' => 'green')),
        ));

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
            array(
                'Deprecated: Elastica\Aggregation\Filters\addFilter() passing filter as AbstractFilter is deprecated. Pass instance of AbstractQuery instead.',
            )
        );
    }

    /**
     * @group unit
     */
    public function testToArrayUsingNamedFilters()
    {
        $expected = array(
            'filters' => array(
                'filters' => array(
                    '' => array(
                        'term' => array('color' => ''),
                    ),
                    '0' => array(
                        'term' => array('color' => '0'),
                    ),
                    'blue' => array(
                        'term' => array('color' => 'blue'),
                    ),
                    'red' => array(
                        'term' => array('color' => 'red'),
                    ),
                ),
            ),
            'aggs' => array(
                'avg_price' => array('avg' => array('field' => 'price')),
            ),
        );

        $agg = new Filters('by_color');

        $agg->addFilter(new Query\Term(array('color' => '')), '');
        $agg->addFilter(new Query\Term(array('color' => '0')), '0');
        $agg->addFilter(new Query\Term(array('color' => 'blue')), 'blue');
        $agg->addFilter(new Query\Term(array('color' => 'red')), 'red');

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
        $expected = array(
            'filters' => array(
                'filters' => array(
                    '' => array(
                        'term' => array('color' => ''),
                    ),
                    '0' => array(
                        'term' => array('color' => '0'),
                    ),
                    'blue' => array(
                        'term' => array('color' => 'blue'),
                    ),
                    'red' => array(
                        'term' => array('color' => 'red'),
                    ),
                ),
            ),
            'aggs' => array(
                'avg_price' => array('avg' => array('field' => 'price')),
            ),
        );

        $agg = new Filters('by_color');

        $this->hideDeprecated();
        $agg->addFilter(new Term(array('color' => '')), '');
        $agg->addFilter(new Term(array('color' => '0')), '0');
        $agg->addFilter(new Term(array('color' => 'blue')), 'blue');
        $agg->addFilter(new Term(array('color' => 'red')), 'red');
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
        $agg->addFilter(new Query\Term(array('color' => '0')), 0);
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
        $agg->addFilter(new Term(array('color' => '0')), 0);
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
        $agg->addFilter(new Query\Term(array('color' => '0')), '0');
        $agg->addFilter(new Query\Term(array('color' => '0')));
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
        $agg->addFilter(new Term(array('color' => '0')), '0');
        $agg->addFilter(new Term(array('color' => '0')));
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

        $agg->addFilter(new Query\Term(array('color' => '0')));
        $agg->addFilter(new Query\Term(array('color' => '0')), '0');
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
        $agg->addFilter(new Term(array('color' => '0')));
        $agg->addFilter(new Term(array('color' => '0')), '0');
        $this->showDeprecated();
    }

    /**
     * @group unit
     */
    public function testToArrayUsingAnonymousFilters()
    {
        $expected = array(
            'filters' => array(
                'filters' => array(
                    array(
                        'term' => array('color' => 'blue'),
                    ),
                    array(
                        'term' => array('color' => 'red'),
                    ),
                ),
            ),
            'aggs' => array(
                'avg_price' => array('avg' => array('field' => 'price')),
            ),
        );

        $agg = new Filters('by_color');

        $agg->addFilter(new Query\Term(array('color' => 'blue')));
        $agg->addFilter(new Query\Term(array('color' => 'red')));

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
        $expected = array(
            'filters' => array(
                'filters' => array(
                    array(
                        'term' => array('color' => 'blue'),
                    ),
                    array(
                        'term' => array('color' => 'red'),
                    ),
                ),
            ),
            'aggs' => array(
                'avg_price' => array('avg' => array('field' => 'price')),
            ),
        );

        $agg = new Filters('by_color');

        $this->hideDeprecated();
        $agg->addFilter(new Term(array('color' => 'blue')));
        $agg->addFilter(new Term(array('color' => 'red')));
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
        $agg->addFilter(new Query\Term(array('color' => 'blue')), 'blue');
        $agg->addFilter(new Query\Term(array('color' => 'red')), 'red');

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
        $agg->addFilter(new Term(array('color' => 'blue')), 'blue');
        $agg->addFilter(new Term(array('color' => 'red')), 'red');
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
