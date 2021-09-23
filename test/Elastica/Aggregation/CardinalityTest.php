<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Cardinality;
use Elastica\Document;
use Elastica\Query;
use Elastica\Type\Mapping;
use Yoast\PHPUnitPolyfills\Polyfills\AssertIsType;

class CardinalityTest extends BaseAggregationTest
{
    use AssertIsType;

    protected function _getIndexForTest()
    {
        $index = $this->_createIndex();

        $mapping = new Mapping($index->getType('_doc'), [
            'color' => [
                'type' => 'keyword',
            ],
        ]);
        $index->getType('_doc')->setMapping($mapping);

        $index->getType('_doc')->addDocuments([
            new Document(1, ['color' => 'blue']),
            new Document(2, ['color' => 'blue']),
            new Document(3, ['color' => 'red']),
            new Document(4, ['color' => 'green']),
        ]);

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testCardinalityAggregation()
    {
        $agg = new Cardinality('cardinality');
        $agg->setField('color');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->_getIndexForTest()->search($query)->getAggregation('cardinality');

        $this->assertEquals(3, $results['value']);
    }

    /**
     * @dataProvider invalidPrecisionThresholdProvider
     * @group unit
     *
     * @param $threshold
     */
    public function testInvalidPrecisionThreshold($threshold)
    {
        $this->expectException(\InvalidArgumentException::class);

        $agg = new Cardinality('threshold');
        $agg->setPrecisionThreshold($threshold);
    }

    /**
     * @dataProvider validPrecisionThresholdProvider
     * @group unit
     *
     * @param $threshold
     */
    public function testPrecisionThreshold($threshold)
    {
        $agg = new Cardinality('threshold');
        $agg->setPrecisionThreshold($threshold);

        $this->assertNotNull($agg->getParam('precision_threshold'));
        self::assertIsInt($agg->getParam('precision_threshold'));
    }

    public function invalidPrecisionThresholdProvider()
    {
        return [
            'string' => ['100'],
            'float' => [7.8],
            'boolean' => [true],
            'array' => [[]],
            'object' => [new \StdClass()],
        ];
    }

    public function validPrecisionThresholdProvider()
    {
        return [
            'negative-int' => [-140],
            'zero' => [0],
            'positive-int' => [150],
            'more-than-max' => [40001],
        ];
    }

    /**
     * @dataProvider validRehashProvider
     * @group unit
     *
     * @param bool $rehash
     */
    public function testRehash($rehash)
    {
        $agg = new Cardinality('rehash');
        $agg->setRehash($rehash);

        $this->assertNotNull($agg->getParam('rehash'));
        self::assertIsBool($agg->getParam('rehash'));
    }

    /**
     * @dataProvider invalidRehashProvider
     * @group unit
     *
     * @param mixed $rehash
     */
    public function testInvalidRehash($rehash)
    {
        $this->expectException(\InvalidArgumentException::class);

        $agg = new Cardinality('rehash');
        $agg->setRehash($rehash);
    }

    public function invalidRehashProvider()
    {
        return [
            'string' => ['100'],
            'int' => [100],
            'float' => [7.8],
            'array' => [[]],
            'object' => [new \StdClass()],
        ];
    }

    public function validRehashProvider()
    {
        return [
            'true' => [true],
            'false' => [false],
        ];
    }
}
