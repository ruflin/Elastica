<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Avg;
use Elastica\Aggregation\Filter;
use Elastica\Document;
use Elastica\Filter\Exists;
use Elastica\Filter\Range;
use Elastica\Filter\Term;
use Elastica\Query;
use Elastica\Query\Range as RangeQuery;
use Elastica\Query\Term as TermQuery;

class FilterTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

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
    public function testConstructorFilterInvalid()
    {
        new Filter('test', $this);
    }

    /**
     * @group unit
     */
    public function testConstructorWithLegacyFilterDeprecated()
    {
        $this->hideDeprecated();
        $existsFilter = new Exists('test');
        $this->showDeprecated();

        $errorsCollector = $this->startCollectErrors();
        new Filter('test', $existsFilter);
        $this->finishCollectErrors();

        $errorsCollector->assertOnlyDeprecatedErrors(
            array(
                'Deprecated: Elastica\Aggregation\Filter passing filter as AbstractFilter is deprecated. Pass instance of AbstractQuery instead.',
                'Deprecated: Elastica\Aggregation\Filter\setFilter() passing filter as AbstractFilter is deprecated. Pass instance of AbstractQuery instead.',
            )
        );
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testSetFilterInvalid()
    {
        $agg = new Filter('test');
        $agg->setFilter($this);
    }

    /**
     * @group unit
     */
    public function testSetFilterWithLegacyFilterDeprecated()
    {
        $this->hideDeprecated();
        $existsFilter = new Exists('test');
        $this->showDeprecated();

        $agg = new Filter('test');

        $errorsCollector = $this->startCollectErrors();
        $agg->setFilter($existsFilter);
        $this->finishCollectErrors();

        $errorsCollector->assertOnlyDeprecatedErrors(
            array(
                'Deprecated: Elastica\Aggregation\Filter\setFilter() passing filter as AbstractFilter is deprecated. Pass instance of AbstractQuery instead.',
            )
        );
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $expected = array(
            'filter' => array('range' => array('stock' => array('gt' => 0))),
            'aggs' => array(
                'avg_price' => array('avg' => array('field' => 'price')),
            ),
        );

        $agg = new Filter('in_stock_products');
        $agg->setFilter(new RangeQuery('stock', array('gt' => 0)));
        $avg = new Avg('avg_price');
        $avg->setField('price');
        $agg->addAggregation($avg);

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @group unit
     */
    public function testToArrayWithLegacy()
    {
        $expected = array(
            'filter' => array('range' => array('stock' => array('gt' => 0))),
            'aggs' => array(
                'avg_price' => array('avg' => array('field' => 'price')),
            ),
        );

        $agg = new Filter('in_stock_products');
        $this->hideDeprecated();
        $agg->setFilter(new Range('stock', array('gt' => 0)));
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
        $agg = new Filter('filter');
        $agg->setFilter(new TermQuery(array('color' => 'blue')));
        $avg = new Avg('price');
        $avg->setField('price');
        $agg->addAggregation($avg);

        $query = new Query();
        $query->addAggregation($agg);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('filter');
        $results = $results['price']['value'];

        $this->assertEquals((5 + 8) / 2.0, $results);
    }

    /**
     * @group functional
     */
    public function testFilterAggregationWithLegacyFilter()
    {
        $agg = new Filter('filter');
        $this->hideDeprecated();
        $agg->setFilter(new Term(array('color' => 'blue')));
        $this->showDeprecated();
        $avg = new Avg('price');
        $avg->setField('price');
        $agg->addAggregation($avg);

        $query = new Query();
        $query->addAggregation($agg);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('filter');
        $results = $results['price']['value'];

        $this->assertEquals((5 + 8) / 2.0, $results);
    }

    /**
     * @group functional
     */
    public function testFilterNoSubAggregation()
    {
        $agg = new Avg('price');
        $agg->setField('price');

        $query = new Query();
        $query->addAggregation($agg);

        $results = $this->_getIndexForTest()->search($query)->getAggregation('price');
        $results = $results['value'];

        $this->assertEquals((5 + 8 + 1 + 3) / 4.0, $results);
    }

    /**
     * @group unit
     */
    public function testConstruct()
    {
        $agg = new Filter('foo', new TermQuery(array('color' => 'blue')));

        $expected = array(
            'filter' => array(
                'term' => array(
                    'color' => 'blue',
                ),
            ),
        );

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @group unit
     */
    public function testConstructWithLegacyFilter()
    {
        $this->hideDeprecated();
        $agg = new Filter('foo', new Term(array('color' => 'blue')));
        $this->showDeprecated();

        $expected = array(
            'filter' => array(
                'term' => array(
                    'color' => 'blue',
                ),
            ),
        );

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @group unit
     */
    public function testConstructWithoutFilter()
    {
        $agg = new Filter('foo');
        $this->assertEquals('foo', $agg->getName());
    }
}
