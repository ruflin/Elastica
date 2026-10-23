<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\AbstractSimpleAggregation;
use Elastica\Exception\InvalidException;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('unit')]
class AbstractSimpleAggregationTest extends BaseAggregationTestCase
{
    private AbstractSimpleAggregation $aggregation;

    protected function setUp(): void
    {
        parent::setUp();

        // A mock would replace toArray(), the method under test, so use a real subclass with no params set.
        $this->aggregation = new class('test') extends AbstractSimpleAggregation {};
    }

    public function testToArrayThrowsExceptionOnUnsetParams(): void
    {
        $this->expectException(InvalidException::class);
        $this->expectExceptionMessage('Either the field param or the script param should be set');

        $this->aggregation->toArray();
    }
}
