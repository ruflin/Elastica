<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\MatchAllFilter;
use Elastica\Test\Base as BaseTest;

class MatchAllTest extends BaseTest
{
    public function testToArray()
    {
        $filter = new MatchAllFilter();

        $expectedArray = array('match_all' => new \stdClass());

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
