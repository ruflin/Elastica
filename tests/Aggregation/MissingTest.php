<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Missing;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;

/**
 * @internal
 */
class MissingTest extends BaseAggregationTest
{
    /**
     * @group functional
     */
    public function testMissingAggregation(): void
    {
        $agg = new Missing('missing', 'color');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('missing');

        $this->assertEquals(1, $results['doc_count']);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();
        $mapping = new Mapping([
            'price' => ['type' => 'keyword'],
            'color' => ['type' => 'keyword'],
        ]);
        $index->setMapping($mapping);

        $index->addDocuments([
            new Document(1, ['price' => 5, 'color' => 'blue']),
            new Document(2, ['price' => 8, 'color' => 'blue']),
            new Document(3, ['price' => 1]),
            new Document(4, ['price' => 3, 'color' => 'green']),
        ]);

        $index->refresh();

        return $index;
    }
}
