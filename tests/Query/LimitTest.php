<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Query\Limit;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class LimitTest extends BaseTest
{
    #[Group('unit')]
    public function testSetType(): void
    {
        $query = new Limit(10);
        $this->assertSame(10, $query->getParam('value'));

        $query->setLimit(20);
        $this->assertSame(20, $query->getParam('value'));
    }

    #[Group('unit')]
    public function testToArray(): void
    {
        $query = new Limit(15);

        $expectedArray = [
            'limit' => ['value' => 15],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }
}
