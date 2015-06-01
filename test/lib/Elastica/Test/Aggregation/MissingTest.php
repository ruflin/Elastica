<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Missing;
use Elastica\Document;
use Elastica\Query;

class MissingTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        $index->getType('test')->addDocuments(array(
            new Document(1, array('price' => 5, 'color' => 'blue')),
            new Document(2, array('price' => 8, 'color' => 'blue')),
            new Document(3, array('price' => 1)),
            new Document(4, array('price' => 3, 'color' => 'green')),
        ));

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
