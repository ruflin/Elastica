<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Avg;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;

/**
 * @internal
 */
class AvgTest extends BaseAggregationTest
{
    /**
     * @group functional
     */
    public function testAvgAggregation(): void
    {
        $agg = new Avg('avg');
        $agg->setField('price');

        $query = new Query();
        $query->addAggregation($agg);

        $resultSet = $this->_getIndexForTest()->search($query);
        $results = $resultSet->getAggregations();

        $this->assertTrue($resultSet->hasAggregations());
        $this->assertEquals((5 + 8 + 1 + 3) / 4.0, $results['avg']['value']);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document(1, ['price' => 5]),
            new Document(2, ['price' => 8]),
            new Document(3, ['price' => 1]),
            new Document(4, ['price' => 3]),
        ]);

        $index->refresh();

        return $index;
    }
}
