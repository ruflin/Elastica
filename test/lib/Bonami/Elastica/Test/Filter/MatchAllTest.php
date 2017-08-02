<?php
namespace Elastica\Test\Filter;

use Bonami\Elastica\Filter\MatchAll;
use Bonami\Elastica\Test\Base as BaseTest;

class MatchAllTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $filter = new MatchAll();

        $expectedArray = array('match_all' => new \stdClass());

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
