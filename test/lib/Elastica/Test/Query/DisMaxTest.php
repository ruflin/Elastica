<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\DisMax;
use Elastica\Query\Ids;
use Elastica\Query\QueryString;
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
                    $idsQuery3->toArray(),
                ),
            ),
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }

    public function testQuery()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('test');

        $doc = new Document(1, array('name' => 'Basel-Stadt'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'New York'));
        $type->addDocument($doc);
        $doc = new Document(3, array('name' => 'Baden'));
        $type->addDocument($doc);
        $doc = new Document(4, array('name' => 'Baden Baden'));
        $type->addDocument($doc);

        $index->refresh();

        $queryString1 = new QueryString('Bade*');
        $queryString2 = new QueryString('Base*');

        $boost = 1.2;
        $tieBreaker = 2;

        $query = new DisMax();
        $query->setBoost($boost);
        $query->setTieBreaker($tieBreaker);
        $query->addQuery($queryString1);
        $query->addQuery($queryString2);
        $resultSet = $type->search($query);

        $this->assertEquals(3, $resultSet->count());
    }
}
