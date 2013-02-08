<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\MatchAll;
use Elastica\Test\Base as BaseTest;

class MatchAllTest extends BaseTest
{
    public function testToArray()
    {
        $filter = new MatchAll();

        $expectedArray = array('match_all' => new \stdClass());

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
