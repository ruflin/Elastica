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
        $this->assertEquals(10, $query->getParam('value'));

        $this->assertInstanceOf(Limit::class, $query->setLimit(20));
        $this->assertEquals(20, $query->getParam('value'));
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
