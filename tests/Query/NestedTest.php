<?php

namespace Elastica\Test\Query;

use Elastica\Query\Nested;
use Elastica\Query\QueryString;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class NestedTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testSetQuery(): void
    {
        $nested = new Nested();
        $path = 'test1';

        $queryString = new QueryString('test');
        $this->assertInstanceOf(Nested::class, $nested->setQuery($queryString));
        $this->assertInstanceOf(Nested::class, $nested->setPath($path));
        $expected = [
            'nested' => [
                'query' => $queryString->toArray(),
                'path' => $path,
            ],
        ];

        $this->assertEquals($expected, $nested->toArray());
    }
}
