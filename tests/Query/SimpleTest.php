<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Query\Simple;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class SimpleTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $testQuery = ['hello' => ['world'], 'name' => 'ruflin'];
        $query = new Simple($testQuery);

        $this->assertEquals($testQuery, $query->toArray());
    }
}
