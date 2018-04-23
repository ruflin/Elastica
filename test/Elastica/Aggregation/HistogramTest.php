<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Histogram;
use Elastica\Document;
use Elastica\Query;

class HistogramTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        $index->getType('test')->addDocuments([
            new Document(1, ['price' => 5, 'color' => 'blue']),
            new Document(2, ['price' => 8, 'color' => 'blue']),
            new Document(3, ['price' => 1, 'color' => 'red']),
            new Document(4, ['price' => 30, 'color' => 'green']),
            new Document(5, ['price' => 40, 'color' => 'red']),
            new Document(6, ['price' => 35, 'color' => 'green']),
            new Document(7, ['price' => 42, 'color' => 'red']),
            new Document(8, ['price' => 41, 'color' => 'blue']),
        ]);

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testHistogramAggregation()
    {
        $agg = new Histogram('hist', 'price', 10);
        $agg->setMinimumDocumentCount(0); // should return empty buckets

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('hist');

        $buckets = $results['buckets'];
        $this->assertCount(5, $buckets);
        $this->assertEquals(30, $buckets[3]['key']);
        $this->assertEquals(2, $buckets[3]['doc_count']);
    }
}
