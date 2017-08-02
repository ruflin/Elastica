<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Range;
use Elastica\Document;
use Elastica\Query;

class RangeTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        $index->getType('test')->addDocuments(array(
            new Document(1, array('price' => 5)),
            new Document(2, array('price' => 8)),
            new Document(3, array('price' => 1)),
            new Document(4, array('price' => 3)),
            new Document(5, array('price' => 1.5)),
            new Document(6, array('price' => 2)),
        ));

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testRangeAggregation()
    {
        $agg = new Range('range');
        $agg->setField('price');
        $agg->addRange(1.5, 5);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('range');

        $this->assertEquals(2, $results['buckets'][0]['doc_count']);
    }

    /**
     * @group unit
     */
    public function testRangeAggregationWithKey()
    {
        $agg = new Range('range');
        $agg->setField('price');
        $agg->addRange(null, 50, 'cheap');
        $agg->addRange(50, 100, 'average');
        $agg->addRange(100, null, 'expensive');

        $expected = array(
            'range' => array(
                'field' => 'price',
                'ranges' => array(
                    array(
                        'to' => 50,
                        'key' => 'cheap',
                    ),
                    array(
                        'from' => 50,
                        'to' => 100,
                        'key' => 'average',
                    ),
                    array(
                        'from' => 100,
                        'key' => 'expensive',
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $agg->toArray());
    }
}
