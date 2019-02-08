<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Missing;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Type\Mapping;

class MissingTest extends BaseAggregationTest
{
    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $mapping = new Mapping($index->getType('_doc'), [
            'price' => ['type' => 'keyword'],
            'color' => ['type' => 'keyword'],
        ]);
        $index->getType('_doc')->setMapping($mapping);

        $index->getType('_doc')->addDocuments([
            new Document(1, ['price' => 5, 'color' => 'blue']),
            new Document(2, ['price' => 8, 'color' => 'blue']),
            new Document(3, ['price' => 1]),
            new Document(4, ['price' => 3, 'color' => 'green']),
        ]);

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testMissingAggregation()
    {
        $agg = new Missing('missing', 'color');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('missing');

        $this->assertEquals(1, $results['doc_count']);
    }
}
