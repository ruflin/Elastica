<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Max;
use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Script\Script;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class MaxTest extends BaseAggregationTestCase
{
    private const MAX_PRICE = 8;

    #[Group('unit')]
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

    #[Group('functional')]
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

    #[Group('functional')]
    public function testMaxAggregationWithMissing(): void
    {
        // feature is buggy on version prior 7.5;
        $this->_checkVersion('7.5');

        $index = $this->_getIndexForTest();

        $agg = new Max('max_price');
        $agg->setField('price');
        $agg->setMissing(42);

        $query = new Query();
        $query->addAggregation($agg);
        $results = $index->search($query)->getAggregation('max_price');

        $this->assertEquals(42, $results['value']);
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();

        $index->addDocuments([
            new Document('1', ['price' => 5]),
            new Document('2', ['price' => self::MAX_PRICE]),
            new Document('3', ['price' => 1]),
            new Document('4', ['price' => 3]),
            new Document('5', ['anything' => 'anything']),
        ]);

        $index->refresh();

        return $index;
    }
}
