<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Query;
use Elastica\Type\Mapping;

class TermsTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        $mapping = new Mapping($index->getType('test'), [
            'color' => ['type' => 'keyword'],
        ]);
        $index->getType('test')->setMapping($mapping);

        $index->getType('test')->addDocuments([
            new Document(1, ['color' => 'blue']),
            new Document(2, ['color' => 'blue']),
            new Document(3, ['color' => 'red']),
            new Document(4, ['color' => 'green']),
        ]);

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testTermsAggregation()
    {
        $agg = new Terms('terms');
        $agg->setField('color');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('terms');

        $this->assertEquals(2, $results['buckets'][0]['doc_count']);
        $this->assertEquals('blue', $results['buckets'][0]['key']);
    }

    /**
     * @group functional
     */
    public function testTermsSetOrder()
    {
        $agg = new Terms('terms');
        $agg->setField('color');
        $agg->setOrder('_count', 'asc');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('terms');

        $this->assertEquals('blue', $results['buckets'][2]['key']);
    }

    /**
     * @group functional
     */
    public function testTermsSetOrders()
    {
        $agg = new Terms('terms');
        $agg->setField('color');
        $agg->setOrders([
            ['_count' => 'asc'], // 1. red,   2. green, 3. blue
            ['_key' => 'asc'],   // 1. green, 2. red,   3. blue
        ]);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('terms');

        $this->assertSame('green', $results['buckets'][0]['key']);
        $this->assertSame('red', $results['buckets'][1]['key']);
        $this->assertSame('blue', $results['buckets'][2]['key']);
    }
}
