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

        $index->getType('test')->addDocuments([
            new Document(1, ['price' => 5]),
            new Document(2, ['price' => 8]),
            new Document(3, ['price' => 1]),
            new Document(4, ['price' => 3]),
            new Document(5, ['price' => 1.5]),
            new Document(6, ['price' => 2]),
        ]);

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

        $expected = [
            'range' => [
                'field' => 'price',
                'ranges' => [
                    [
                        'to' => 50,
                        'key' => 'cheap',
                    ],
                    [
                        'from' => 50,
                        'to' => 100,
                        'key' => 'average',
                    ],
                    [
                        'from' => 100,
                        'key' => 'expensive',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, $agg->toArray());
    }
}
