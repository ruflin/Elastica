<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Avg;
use Elastica\Aggregation\Filter;
use Elastica\Aggregation\Filters;
use Elastica\Document;
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
        $agg->addFilter(new Term(array('color' => '')), '');
        $agg->addFilter(new Term(array('color' => '0')), '0');
        $agg->addFilter(new Term(array('color' => 'blue')), 'blue');
        $agg->addFilter(new Term(array('color' => 'red')), 'red');

        $avg = new Avg('avg_price');
        $avg->setField('price');
        $agg->addAggregation($avg);

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @group unit
     * @expectedException Elastica\Exception\InvalidException
     * @expectedExceptionMessage Name must be a string
     */
    public function testWrongName()
    {
        $agg = new Filters('by_color');
        $agg->addFilter(new Term(array('color' => '0')), 0);
    }

    /**
     * @group unit
     * @expectedException Elastica\Exception\InvalidException
     * @expectedExceptionMessage Mix named and anonymous keys are not allowed
     */
    public function testMixNamedAndAnonymousFilters()
    {
        $agg = new Filters('by_color');
        $agg->addFilter(new Term(array('color' => '0')), '0');
        $agg->addFilter(new Term(array('color' => '0')));
    }

    /**
     * @group unit
     * @expectedException Elastica\Exception\InvalidException
     * @expectedExceptionMessage Mix named and anonymous keys are not allowed
     */
    public function testMixAnonymousAndNamedFilters()
    {
        $agg = new Filters('by_color');
        $agg->addFilter(new Term(array('color' => '0')));
        $agg->addFilter(new Term(array('color' => '0')), '0');
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
        $agg->addFilter(new Term(array('color' => 'blue')));
        $agg->addFilter(new Term(array('color' => 'red')));

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
        $agg->addFilter(new Term(array('color' => 'blue')), 'blue');
        $agg->addFilter(new Term(array('color' => 'red')), 'red');

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
