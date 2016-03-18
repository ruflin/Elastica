<?php

namespace Elastica\Test\Aggregation;

class AbstractSimpleAggregationTest extends BaseAggregationTest
{
    public function setUp()
    {
        $this->aggregation = $this->getMockForAbstractClass(
            'Elastica\Aggregation\AbstractSimpleAggregation',
            array('whatever')
        );
    }

    public function testToArrayThrowsExceptionOnUnsetParams()
    {
        $this->setExpectedException(
            'Elastica\Exception\InvalidException',
            'Either the field param or the script param should be set'
        );

        $this->aggregation->toArray();
    }
}
