<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Percentiles;
use Elastica\Document;
use Elastica\Query;

/**
 * @internal
 */
class PercentilesTest extends BaseAggregationTest
{
    /**
     * @group functional
     */
    public function testConstruct(): void
    {
        $aggr = new Percentiles('price_percentile');
        $this->assertEquals('price_percentile', $aggr->getName());

        $aggr = new Percentiles('price_percentile', 'price');
        $this->assertEquals('price', $aggr->getParam('field'));
    }

    /**
     * @group functional
     */
    public function testSetField(): void
    {
        $aggr = new Percentiles('price_percentile');
        $aggr->setField('price');

        $this->assertEquals('price', $aggr->getParam('field'));
        $this->assertInstanceOf(Percentiles::class, $aggr->setField('price'));
    }

    /**
     * @group unit
     */
    public function testCompression(): void
    {
        $expected = [
            'percentiles' => [
                'field' => 'price',
                'keyed' => false,
                'tdigest' => [
                    'compression' => 100,
                ],
            ],
        ];
        $aggr = new Percentiles('price_percentile');
        $aggr->setField('price');
        $aggr->setKeyed(false);
        $aggr->setCompression(100);

        $this->assertEquals($expected, $aggr->toArray());
    }

    /**
     * @group unit
     */
    public function testHdr(): void
    {
        $expected = [
            'percentiles' => [
                'field' => 'price',
                'keyed' => false,
                'hdr' => [
                    'number_of_significant_value_digits' => 2.0,
                ],
            ],
        ];
        $aggr = new Percentiles('price_percentile');
        $aggr->setField('price');
        $aggr->setKeyed(false);
        $aggr->setHdr('number_of_significant_value_digits', 2);

        $this->assertEquals($expected, $aggr->toArray());
    }

    /**
     * @group functional
     */
    public function testSetPercents(): void
    {
        $percents = [1, 2, 3];
        $aggr = new Percentiles('price_percentile');
        $aggr->setPercents($percents);
        $this->assertEquals($percents, $aggr->getParam('percents'));
        $this->assertInstanceOf(Percentiles::class, $aggr->setPercents($percents));
    }

    /**
     * @group functional
     */
    public function testAddPercent(): void
    {
        $percents = [1, 2, 3];
        $aggr = new Percentiles('price_percentile');
        $aggr->setPercents($percents);
        $this->assertEquals($percents, $aggr->getParam('percents'));
        $aggr->addPercent(4);
        $percents[] = 4;
        $this->assertEquals($percents, $aggr->getParam('percents'));
        $this->assertInstanceOf(Percentiles::class, $aggr->addPercent(4));
    }

    /**
     * @group functional
     */
    public function testSetScript(): void
    {
        $script = 'doc["load_time"].value / 20';
        $aggr = new Percentiles('price_percentile');
        $aggr->setScript($script);
        $this->assertEquals($script, $aggr->getParam('script'));
        $this->assertInstanceOf(Percentiles::class, $aggr->setScript($script));
    }

    /**
     * @group functional
     */
    public function testActualWork(): void
    {
        // prepare
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document(1, ['price' => 100]),
            new Document(2, ['price' => 200]),
            new Document(3, ['price' => 300]),
            new Document(4, ['price' => 400]),
            new Document(5, ['price' => 500]),
            new Document(6, ['price' => 600]),
            new Document(7, ['price' => 700]),
            new Document(8, ['price' => 800]),
            new Document(9, ['price' => 900]),
            new Document(10, ['price' => 1000]),
        ]);
        $index->refresh();

        // execute
        $aggr = new Percentiles('price_percentile');
        $aggr->setField('price');

        $query = new Query();
        $query->addAggregation($aggr);

        $resultSet = $index->search($query);
        $aggrResult = $resultSet->getAggregation('price_percentile');

        $this->assertEquals(100.0, $aggrResult['values']['1.0']);
        $this->assertEquals(100.0, $aggrResult['values']['5.0']);
        $this->assertEquals(300.0, $aggrResult['values']['25.0']);
        $this->assertEquals(550.0, $aggrResult['values']['50.0']);
        $this->assertEquals(800.0, $aggrResult['values']['75.0']);
        $this->assertEquals(1000.0, $aggrResult['values']['95.0']);
        $this->assertEquals(1000.0, $aggrResult['values']['99.0']);
    }

    /**
     * @group functional
     */
    public function testKeyed(): void
    {
        $expected = [
            'values' => [
                [
                    'key' => 1,
                    'value' => 100,
                ],
                [
                    'key' => 5,
                    'value' => 100,
                ],
                [
                    'key' => 25,
                    'value' => 300,
                ],
                [
                    'key' => 50,
                    'value' => 550,
                ],
                [
                    'key' => 75,
                    'value' => 800,
                ],
                [
                    'key' => 95,
                    'value' => 1000,
                ],
                [
                    'key' => 99,
                    'value' => 1000,
                ],
            ],
        ];

        // prepare
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document(1, ['price' => 100]),
            new Document(2, ['price' => 200]),
            new Document(3, ['price' => 300]),
            new Document(4, ['price' => 400]),
            new Document(5, ['price' => 500]),
            new Document(6, ['price' => 600]),
            new Document(7, ['price' => 700]),
            new Document(8, ['price' => 800]),
            new Document(9, ['price' => 900]),
            new Document(10, ['price' => 1000]),
        ]);
        $index->refresh();

        // execute
        $aggr = new Percentiles('price_percentile');
        $aggr->setField('price');
        $aggr->setKeyed(false);

        $query = new Query();
        $query->addAggregation($aggr);

        $resultSet = $index->search($query);
        $aggrResult = $resultSet->getAggregation('price_percentile');

        $this->assertEquals($expected, $aggrResult);
    }

    /**
     * @group unit
     */
    public function testMissing(): void
    {
        $expected = [
            'percentiles' => [
                'field' => 'price',
                'keyed' => false,
                'hdr' => [
                    'number_of_significant_value_digits' => 2.0,
                ],
                'missing' => 10,
            ],
        ];
        $aggr = new Percentiles('price_percentile');
        $aggr->setField('price');
        $aggr->setKeyed(false);
        $aggr->setHdr('number_of_significant_value_digits', 2);
        $aggr->setMissing(10);

        $this->assertEquals($expected, $aggr->toArray());
    }
}
