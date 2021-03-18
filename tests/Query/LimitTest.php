<?php

namespace Elastica\Test\Query;

use Elastica\Query\Limit;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class LimitTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testSetType(): void
    {
        $query = new Limit(10);
        $this->assertSame(10, $query->getParam('value'));

        $query->setLimit(20);
        $this->assertSame(20, $query->getParam('value'));
    }

    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $query = new Limit(15);

        $expectedArray = [
            'limit' => ['value' => 15],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }
}
