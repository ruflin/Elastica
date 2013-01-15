<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\Ids;
use Elastica\Filter\Not;
use Elastica\Test\Base as BaseTest;

class NotTest extends BaseTest
{
    public function testToArray()
    {
        $idsFilter = new Ids();
        $idsFilter->setIds(12);
        $filter = new Not($idsFilter);

        $expectedArray = array(
            'not' => array(
                'filter' => $idsFilter->toArray()
            )
        );

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
