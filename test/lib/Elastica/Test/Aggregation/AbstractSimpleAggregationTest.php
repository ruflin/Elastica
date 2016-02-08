<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\AbstractSimpleAggregation;
use Elastica\Exception\InvalidException;

class AbstractSimpleAggregationTest extends BaseAggregationTest
{
    public function setUp()
    {
        $this->aggregation = $this->getMockForAbstractClass(
            'Elastica\Aggregation\AbstractSimpleAggregation',
            ['whatever']
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
