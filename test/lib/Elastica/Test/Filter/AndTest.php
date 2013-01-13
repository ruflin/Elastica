<?php

namespace Elastica\Test\Filter;

use Elastica\Document;
use Elastica\Filter\AndFilter;
use Elastica\Filter\IdsFilter;
use Elastica\Test\Base as BaseTest;

class AndTest extends BaseTest
{
    public function testToArray()
    {
        $and = new AndFilter();
        $this->assertEquals(array('and' => array()), $and->toArray());

        $idsFilter = new IdsFilter();
        $idsFilter->setIds(12);

        $and->addFilter($idsFilter);
        $and->addFilter($idsFilter);

        $expectedArray = array(
            'and' => array(
                $idsFilter->toArray(),
                $idsFilter->toArray()
            )
        );

        $this->assertEquals($expectedArray, $and->toArray());
    }

    public function testSetCache()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('test');

        $doc = new Document(1, array('name' => 'hello world'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'nicolas ruflin'));
        $type->addDocument($doc);
        $doc = new Document(3, array('name' => 'ruflin'));
        $type->addDocument($doc);

        $and = new AndFilter();

        $idsFilter1 = new IdsFilter();
        $idsFilter1->setIds(1);

        $idsFilter2 = new IdsFilter();
        $idsFilter2->setIds(1);

        $and->addFilter($idsFilter1);
        $and->addFilter($idsFilter2);

        $index->refresh();
        $and->setCached(true);

        $resultSet = $type->search($and);

        $this->assertEquals(1, $resultSet->count());
    }
}
