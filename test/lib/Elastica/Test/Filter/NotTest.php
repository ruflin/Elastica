<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\IdsFilter;
use Elastica\Filter\NotFilter;
use Elastica\Test\Base as BaseTest;

class NotTest extends BaseTest
{
    public function testToArray()
    {
        $idsFilter = new IdsFilter();
        $idsFilter->setIds(12);
        $filter = new NotFilter($idsFilter);

        $expectedArray = array(
            'not' => array(
                'filter' => $idsFilter->toArray()
            )
        );

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
