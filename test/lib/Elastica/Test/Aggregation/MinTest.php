<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Min;
use Elastica\Document;
use Elastica\Query;

class MinTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        $index->getType('test')->addDocuments(array(
            new Document(1, array('price' => 5)),
            new Document(2, array('price' => 8)),
            new Document(3, array('price' => 1)),
            new Document(4, array('price' => 3)),
        ));

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testMinAggregation()
    {
        $agg = new Min('min_price');
        $agg->setField('price');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('min_price');

        $this->assertEquals(1, $results['value']);
    }
}
