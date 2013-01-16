<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\Ids;
use Elastica\Filter\BoolNot;
use Elastica\Test\Base as BaseTest;

class BoolNotTest extends BaseTest
{
    public function testToArray()
    {
        $idsFilter = new Ids();
        $idsFilter->setIds(12);
        $filter = new BoolNot($idsFilter);

        $expectedArray = array(
            'not' => array(
                'filter' => $idsFilter->toArray()
            )
        );

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
