<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Max;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Script\Script;

/**
 * @internal
 */
class MaxTest extends BaseAggregationTest
{
    private const MAX_PRICE = 8;

    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $expected = [
            'max' => [
                'field' => 'price',
                'script' => [
                    'source' => '_value * params.conversion_rate',
                    'params' => [
                        'conversion_rate' => 1.2,
                    ],
                ],
            ],
            'aggs' => [
                'subagg' => ['max' => ['field' => 'foo']],
            ],
        ];

        $agg = new Max('max_price_in_euros');
        $agg->setField('price');
        $agg->setScript(new Script('_value * params.conversion_rate', ['conversion_rate' => 1.2]));
        $max = new Max('subagg');
        $max->setField('foo');
        $agg->addAggregation($max);

        $this->assertEquals($expected, $agg->toArray());
    }

    /**
     * @group functional
     */
    public function testMaxAggregation(): void
    {
        $index = $this->_getIndexForTest();

        $agg = new Max('max_price');
        $agg->setField('price');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $index->search($query)->getAggregation('max_price');

        $this->assertEquals(self::MAX_PRICE, $results['value']);

        // test using a script
        $agg->setScript(new Script('_value * params.conversion_rate', ['conversion_rate' => 1.2], Script::LANG_PAINLESS));
        $query = new Query();
        $query->addAggregation($agg);
        $results = $index->search($query)->getAggregation('max_price');

        $this->assertEquals(self::MAX_PRICE * 1.2, $results['value']);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document(1, ['price' => 5]),
            new Document(2, ['price' => self::MAX_PRICE]),
            new Document(3, ['price' => 1]),
            new Document(4, ['price' => 3]),
        ]);

        $index->refresh();

        return $index;
    }
}
