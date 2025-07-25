<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\WeightedAvg;
use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Index;
use Elastica\Query;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class WeightedAvgTest extends BaseAggregationTestCase
{
    #[Group('functional')]
    public function testWeightedAvgAggregation(): void
    {
        $agg = new WeightedAvg('weighted');
        $agg->setValue('price');
        $weightWhenMissing = 2;
        $agg->setWeight('weight', $weightWhenMissing);

        $query = new Query();
        $query->addAggregation($agg);

        $resultSet = $this->_getIndexForTest()->search($query);
        $results = $resultSet->getAggregations();

        $this->assertTrue($resultSet->hasAggregations());
        $this->assertEquals((5 * 3 + 8 + 1 + 3 * $weightWhenMissing) / 7.0, $results['weighted']['value']);
    }

    #[Group('unit')]
    public function testItsNotPossibleToMixValueFieldAndScript(): void
    {
        $agg = new WeightedAvg('weighted');
        $agg->setValue('price');

        $this->expectExceptionObject(new InvalidException('Weighted Average aggregation with a value mixing field and script is not possible.'));
        $agg->setValueScript('doc.price.value + 1');
    }

    #[Group('unit')]
    public function testItsNotPossibleToMixValueScriptAndField(): void
    {
        $agg = new WeightedAvg('weighted');
        $agg->setValueScript('doc.price.value + 1');

        $this->expectExceptionObject(new InvalidException('Weighted Average aggregation with a value mixing field and script is not possible.'));
        $agg->setValue('price');
    }

    #[Group('unit')]
    public function testItsNotPossibleToMixWeightFieldAndScript(): void
    {
        $agg = new WeightedAvg('weighted');
        $agg->setWeight('weight');

        $this->expectExceptionObject(new InvalidException('Weighted Average aggregation with a weight mixing field and script is not possible.'));
        $agg->setWeightScript('doc.weight.value + 1');
    }

    #[Group('unit')]
    public function testItsNotPossibleToMixWeightScriptAndField(): void
    {
        $agg = new WeightedAvg('weighted');
        $agg->setWeightScript('doc.weight.value + 1');

        $this->expectExceptionObject(new InvalidException('Weighted Average aggregation with a weight mixing field and script is not possible.'));
        $agg->setWeight('weight');
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();
        $index->addDocuments([
            new Document('1', ['price' => 5, 'weight' => 3]),
            new Document('2', ['price' => 8, 'weight' => 1]),
            new Document('3', ['price' => 1, 'weight' => 1]),
            new Document('4', ['price' => 3]),
        ]);

        $index->refresh();

        return $index;
    }
}
