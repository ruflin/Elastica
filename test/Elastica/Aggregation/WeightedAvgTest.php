<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\WeightedAvg;
use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Query;

class WeightedAvgTest extends BaseAggregationTest
{
    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        $index->getType('_doc')->addDocuments([
            new Document(1, ['price' => 5, 'weight' => 3]),
            new Document(2, ['price' => 8, 'weight' => 1]),
            new Document(3, ['price' => 1, 'weight' => 1]),
            new Document(4, ['price' => 3]),
        ]);

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testWeightedAvgAggregation()
    {
        $this->_checkVersion('6.4');

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

    /**
     * @group unit
     */
    public function testItsNotPossibleToMixValueFieldAndScript()
    {
        $agg = new WeightedAvg('weighted');
        $agg->setValue('price');

        $this->expectExceptionObject(new InvalidException('Weighted Average aggregation with a value mixing field and script is not possible.'));
        $agg->setValueScript('doc.price.value + 1');
    }

    /**
     * @group unit
     */
    public function testItsNotPossibleToMixValueScriptAndField()
    {
        $agg = new WeightedAvg('weighted');
        $agg->setValueScript('doc.price.value + 1');

        $this->expectExceptionObject(new InvalidException('Weighted Average aggregation with a value mixing field and script is not possible.'));
        $agg->setValue('price');
    }

    /**
     * @group unit
     */
    public function testItsNotPossibleToMixWeightFieldAndScript()
    {
        $agg = new WeightedAvg('weighted');
        $agg->setWeight('weight');

        $this->expectExceptionObject(new InvalidException('Weighted Average aggregation with a weight mixing field and script is not possible.'));
        $agg->setWeightScript('doc.weight.value + 1');
    }

    /**
     * @group unit
     */
    public function testItsNotPossibleToMixWeightScriptAndField()
    {
        $agg = new WeightedAvg('weighted');
        $agg->setWeightScript('doc.weight.value + 1');

        $this->expectExceptionObject(new InvalidException('Weighted Average aggregation with a weight mixing field and script is not possible.'));
        $agg->setWeight('weight');
    }
}
