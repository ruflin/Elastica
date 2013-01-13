<?php

namespace Elastica\Test\Query;

use Elastica\Query\NestedQuery;
use Elastica\Query\QueryStringQuery;
use Elastica\Test\Base as BaseTest;

class NestedTest extends BaseTest
{
    public function testSetQuery()
    {
        $nested = new NestedQuery();
        $path = 'test1';

        $queryString = new QueryStringQuery('test');
        $this->assertInstanceOf('Elastica\Query\NestedQuery', $nested->setQuery($queryString));
        $this->assertInstanceOf('Elastica\Query\NestedQuery', $nested->setPath($path));
        $expected = array(
            'nested' => array(
                'query' => $queryString->toArray(),
                'path' => $path,
            )
        );

        $this->assertEquals($expected, $nested->toArray());
    }
}
