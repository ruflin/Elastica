<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Query\Nested;
use Elastica\Query\QueryString;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class NestedTest extends BaseTest
{
    #[Group('unit')]
    public function testSetQuery(): void
    {
        $queryString = new QueryString('test');
        $path = 'test1';

        $nested = (new Nested())
            ->setQuery($queryString)
            ->setPath($path)
        ;

        $expected = [
            'nested' => [
                'query' => $queryString->toArray(),
                'path' => $path,
            ],
        ];

        $this->assertSame($expected, $nested->toArray());
    }
}
