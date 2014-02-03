<?php

namespace Elastica\Test\Query;

use Elastica\Query\DisMax;
use Elastica\Query\Ids;
use Elastica\Test\Base as BaseTest;

class DisMaxTest extends BaseTest
{
    public function testToArray()
    {
        $query = new DisMax();

        $idsQuery1 = new Ids();
        $idsQuery1->setIds(1);

        $idsQuery2 = new Ids();
        $idsQuery2->setIds(2);

        $idsQuery3 = new Ids();
        $idsQuery3->setIds(3);

        $boost = 1.2;
        $tieBreaker = 2;

        $query->setBoost($boost);
        $query->setTieBreaker($tieBreaker);
        $query->addQuery($idsQuery1);
        $query->addQuery($idsQuery2);
        $query->addQuery($idsQuery3->toArray());

        $expectedArray = array(
            'dis_max' => array(
                'tie_breaker' => $tieBreaker,
                'boost' => $boost,
                'queries' => array(
                    $idsQuery1->toArray(),
                    $idsQuery2->toArray(),
                    $idsQuery3->toArray()
                )
            )
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }
}
