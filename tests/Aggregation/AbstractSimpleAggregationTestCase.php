<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\AbstractSimpleAggregation;
use Elastica\Exception\InvalidException;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @internal
 */
class AbstractSimpleAggregationTestCase extends BaseAggregationTestCase
{
    /**
     * @var AbstractSimpleAggregation&MockObject
     */
    private $aggregation;

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
