<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Percentiles;
use Elastica\Document;
use Elastica\Query;

class PercentilesTest extends BaseAggregationTest
{
    /**
     * @group functional
     */
    public function testConstruct()
    {
        $aggr = new Percentiles('price_percentile');
        $this->assertEquals('price_percentile', $aggr->getName());

        $aggr = new Percentiles('price_percentile', 'price');
        $this->assertEquals('price', $aggr->getParam('field'));
    }

    /**
     * @group functional
     */
    public function testSetField()
    {
        $aggr = new Percentiles('price_percentile');
        $aggr->setField('price');

        $this->assertEquals('price', $aggr->getParam('field'));
        $this->assertInstanceOf('Elastica\Aggregation\Percentiles', $aggr->setField('price'));
    }

    /**
     * @group functional
     */
    public function testSetCompression()
    {
        $aggr = new Percentiles('price_percentile');
        $aggr->setCompression(200);
        $this->assertEquals(200, $aggr->getParam('compression'));
        $this->assertInstanceOf('Elastica\Aggregation\Percentiles', $aggr->setCompression(200));
    }

    /**
     * @group functional
     */
    public function testSetPercents()
    {
        $percents = array(1, 2, 3);
        $aggr = new Percentiles('price_percentile');
        $aggr->setPercents($percents);
        $this->assertEquals($percents, $aggr->getParam('percents'));
        $this->assertInstanceOf('Elastica\Aggregation\Percentiles', $aggr->setPercents($percents));
    }

    /**
     * @group functional
     */
    public function testAddPercent()
    {
        $percents = array(1, 2, 3);
        $aggr = new Percentiles('price_percentile');
        $aggr->setPercents($percents);
        $this->assertEquals($percents, $aggr->getParam('percents'));
        $aggr->addPercent(4);
        $percents[] = 4;
        $this->assertEquals($percents, $aggr->getParam('percents'));
        $this->assertInstanceOf('Elastica\Aggregation\Percentiles', $aggr->addPercent(4));
    }

    /**
     * @group functional
     */
    public function testSetScript()
    {
        $script = 'doc["load_time"].value / 20';
        $aggr = new Percentiles('price_percentile');
        $aggr->setScript($script);
        $this->assertEquals($script, $aggr->getParam('script'));
        $this->assertInstanceOf('Elastica\Aggregation\Percentiles', $aggr->setScript($script));
    }

    /**
     * @group functional
     */
    public function testActualWork()
    {
        // prepare
        $index = $this->_createIndex();
        $type = $index->getType('offer');
        $type->addDocuments(array(
            new Document(1, array('price' => 100)),
            new Document(2, array('price' => 200)),
            new Document(3, array('price' => 300)),
            new Document(4, array('price' => 400)),
            new Document(5, array('price' => 500)),
            new Document(6, array('price' => 600)),
            new Document(7, array('price' => 700)),
            new Document(8, array('price' => 800)),
            new Document(9, array('price' => 900)),
            new Document(10, array('price' => 1000)),
        ));
        $index->refresh();

        // execute
        $aggr = new Percentiles('price_percentile');
        $aggr->setField('price');

        $query = new Query();
        $query->addAggregation($aggr);

        $resultSet = $type->search($query);
        $aggrResult = $resultSet->getAggregation('price_percentile');

        // hope it's ok to hardcode results...
        $this->assertEquals(109.0, $aggrResult['values']['1.0']);
        $this->assertEquals(145.0, $aggrResult['values']['5.0']);
        $this->assertEquals(325.0, $aggrResult['values']['25.0']);
        $this->assertEquals(550.0, $aggrResult['values']['50.0']);
        $this->assertEquals(775.0, $aggrResult['values']['75.0']);
        $this->assertEquals(955.0, $aggrResult['values']['95.0']);
        $this->assertEquals(991.0, $aggrResult['values']['99.0']);
    }
}
