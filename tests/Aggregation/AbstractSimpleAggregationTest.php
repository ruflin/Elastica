<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\AbstractSimpleAggregation;
use Elastica\Exception\InvalidException;

/**
 * @internal
 */
class AbstractSimpleAggregationTest extends BaseAggregationTest
{
    protected function setUp(): void
    {
        $this->aggregation = $this->getMockForAbstractClass(
            AbstractSimpleAggregation::class,
            ['whatever']
        );
    }

    public function testToArrayThrowsExceptionOnUnsetParams(): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('Either the field param or the script param should be set');

        $this->aggregation->toArray();
    }
}
