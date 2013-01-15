<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\AbstractFilter;
use Elastica\Filter\OrFilter;
use Elastica\Filter\Ids;
use Elastica\Test\Base as BaseTest;

class OrTest extends BaseTest
{
    public function testAddFilter()
    {
        $filter = $this->getMockForAbstractClass('Elastica\Filter\AbstractFilter');
        $orFilter = new OrFilter();
        $returnValue = $orFilter->addFilter($filter);
        $this->assertInstanceOf('Elastica\Filter\OrFilter', $returnValue);
    }

    public function testToArray()
    {
        $orFilter = new OrFilter();

        $filter1 = new Ids();
        $filter1->setIds('1');

        $filter2 = new Ids();
        $filter2->setIds('2');

        $orFilter->addFilter($filter1);
        $orFilter->addFilter($filter2);

        $expectedArray = array(
            'or' => array(
                    $filter1->toArray(),
                    $filter2->toArray()
                )
            );

        $this->assertEquals($expectedArray, $orFilter->toArray());
    }
}
