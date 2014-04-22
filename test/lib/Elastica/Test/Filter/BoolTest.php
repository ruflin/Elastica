<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\Bool;
use Elastica\Filter\Ids;
use Elastica\Test\Base as BaseTest;

class BoolTest extends BaseTest
{
    public function testToArray()
    {
        $mainBool = new Bool();

        $idsFilter1 = new Ids();
        $idsFilter1->setIds(1);
        $idsFilter2 = new Ids();
        $idsFilter2->setIds(2);
        $idsFilter3 = new Ids();
        $idsFilter3->setIds(3);

        $childBool = new Bool();
        $childBool->addShould(array($idsFilter1, $idsFilter2));
        $mainBool->addShould(array($childBool, $idsFilter3));

        $expectedArray = array(
            'bool' => array(
                'should' => array(
                    array(
                        array(
                            'bool' => array(
                                'should' => array(
                                    array(
                                        $idsFilter1->toArray(),
                                        $idsFilter2->toArray()
                                    )
                                )
                            )
                        ),
                        $idsFilter3->toArray()
                    )
                )
            )
        );

        $this->assertEquals($expectedArray, $mainBool->toArray());
    }
}
