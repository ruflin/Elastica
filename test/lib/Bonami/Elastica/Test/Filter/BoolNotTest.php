<?php
namespace Elastica\Test\Filter;

use Bonami\Elastica\Filter\BoolNot;
use Bonami\Elastica\Filter\Ids;
use Bonami\Elastica\Test\Base as BaseTest;

class BoolNotTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $idsFilter = new Ids();
        $idsFilter->setIds(12);
        $filter = new BoolNot($idsFilter);

        $expectedArray = array(
            'not' => array(
                'filter' => $idsFilter->toArray(),
            ),
        );

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
