<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Percentiles;
use Elastica\Document;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class PercentilesTest extends BaseAggregationTestCase
{
    #[Group('functional')]
    public function testConstruct(): void
    {
        $agg = new Percentiles('price_percentile');
        $this->assertSame('price_percentile', $agg->getName());

        $agg = new Percentiles('price_percentile', 'price');
        $this->assertSame('price', $agg->getParam('field'));
    }

    #[Group('functional')]
    public function testSetField(): void
    {
        $agg = (new Percentiles('price_percentile'))
            ->setField('price')
        ;

        $this->assertSame('price', $agg->getParam('field'));
    }

    #[Group('unit')]
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

        $agg = (new Percentiles('price_percentile', 'price'))
            ->setKeyed(false)
            ->setCompression(100)
        ;

        $this->assertEquals($expected, $agg->toArray());
    }

    #[Group('unit')]
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

        $agg = (new Percentiles('price_percentile', 'price'))
            ->setKeyed(false)
            ->setHdr('number_of_significant_value_digits', 2)
        ;

        $this->assertEquals($expected, $agg->toArray());
    }

    #[Group('functional')]
    public function testSetPercents(): void
    {
        $percents = [1, 2, 3];
        $agg = (new Percentiles('price_percentile'))
            ->setPercents($percents)
        ;

        $this->assertSame($percents, $agg->getParam('percents'));
    }

    #[Group('functional')]
    public function testAddPercent(): void
    {
        $percents = [1, 2, 3];
        $agg = (new Percentiles('price_percentile'))
            ->setPercents($percents)
        ;

        $this->assertEquals($percents, $agg->getParam('percents'));

        $agg->addPercent($percents[] = 4);
        $this->assertEquals($percents, $agg->getParam('percents'));
    }

    #[Group('functional')]
    public function testSetScript(): void
    {
        $script = 'doc["load_time"].value / 20';
        $agg = (new Percentiles('price_percentile'))
            ->setScript($script)
        ;

        $this->assertEquals($script, $agg->getParam('script'));
    }

    #[Group('functional')]
    public function testActualWork(): void
    {
        // prepare
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document('1', ['price' => 100]),
            new Document('2', ['price' => 200]),
            new Document('3', ['price' => 300]),
            new Document('4', ['price' => 400]),
            new Document('5', ['price' => 500]),
            new Document('6', ['price' => 600]),
            new Document('7', ['price' => 700]),
            new Document('8', ['price' => 800]),
            new Document('9', ['price' => 900]),
            new Document('10', ['price' => 1000]),
        ]);
        $index->refresh();

        // execute
        $agg = (new Percentiles('price_percentile', 'price'));

        if (isset($_SERVER['ES_VERSION']) && \version_compare($_SERVER['ES_VERSION'], '8.9.0', '>=')) {
            $agg->setParam('tdigest', ['execution_hint' => 'high_accuracy']);
        }

        // execute
        $query = new Query();
        $query->addAggregation($agg);

        $resultSet = $index->search($query);
        $aggResult = $resultSet->getAggregation('price_percentile');

        $this->assertEquals(100.0, $aggResult['values']['1.0']);
        $this->assertEquals(100.0, $aggResult['values']['5.0']);
        $this->assertEquals(300.0, $aggResult['values']['25.0']);
        $this->assertEquals(550.0, $aggResult['values']['50.0']);
        $this->assertEquals(800.0, $aggResult['values']['75.0']);
        $this->assertEquals(1000.0, $aggResult['values']['95.0']);
        $this->assertEquals(1000.0, $aggResult['values']['99.0']);
    }

    #[Group('functional')]
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
            new Document('1', ['price' => 100]),
            new Document('2', ['price' => 200]),
            new Document('3', ['price' => 300]),
            new Document('4', ['price' => 400]),
            new Document('5', ['price' => 500]),
            new Document('6', ['price' => 600]),
            new Document('7', ['price' => 700]),
            new Document('8', ['price' => 800]),
            new Document('9', ['price' => 900]),
            new Document('10', ['price' => 1000]),
        ]);
        $index->refresh();

        // execute
        $agg = (new Percentiles('price_percentile', 'price'))
            ->setKeyed(false)
        ;

        if (isset($_SERVER['ES_VERSION']) && \version_compare($_SERVER['ES_VERSION'], '8.9.0', '>=')) {
            $agg->setParam('tdigest', ['execution_hint' => 'high_accuracy']);
        }

        $query = new Query();
        $query->addAggregation($agg);

        $resultSet = $index->search($query);
        $aggResult = $resultSet->getAggregation('price_percentile');

        $this->assertEquals($expected, $aggResult);
    }

    #[Group('unit')]
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

        $agg = (new Percentiles('price_percentile', 'price'))
            ->setKeyed(false)
            ->setHdr('number_of_significant_value_digits', 2)
            ->setMissing(10)
        ;

        $this->assertEquals($expected, $agg->toArray());
    }
}
