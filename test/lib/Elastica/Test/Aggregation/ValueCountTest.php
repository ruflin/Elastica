<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\ValueCount;
use Elastica\Document;
use Elastica\Query;

class ValueCountTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        $index->getType('test')->addDocuments(array(
            new Document(1, array('price' => 5)),
            new Document(2, array('price' => 8)),
            new Document(3, array('price' => 1)),
            new Document(4, array('price' => 3)),
            new Document(5, array('price' => 3)),
        ));

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testValueCountAggregation()
    {
        $agg = new ValueCount('count', 'price');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('count');

        $this->assertEquals(5, $results['value']);
    }
}
