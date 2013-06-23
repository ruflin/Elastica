<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\Terms;
use Elastica\Test\Base as BaseTest;

class TermsTest extends BaseTest
{

    public function testLookup()
    {
        $index = $this->_createIndex('terms_filter_test');
        $type1 = $index->getType('musicians');
        $type2 = $index->getType('bands');

        //index some test data
        $type1->addDocument(new \Elastica\Document(1, array('name' => 'robert', 'lastName' => 'plant')));
        $type1->addDocument(new \Elastica\Document(2, array('name' => 'jimmy', 'lastName' => 'page')));
        $type1->addDocument(new \Elastica\Document(3, array('name' => 'john paul', 'lastName' => 'jones')));
        $type1->addDocument(new \Elastica\Document(4, array('name' => 'john', 'lastName' => 'bonham')));
        $type1->addDocument(new \Elastica\Document(5, array('name' => 'jimi', 'lastName' => 'hendrix')));

        $type2->addDocument(new \Elastica\Document('led zeppelin', array('members' => array('plant', 'page', 'jones', 'bonham'))));
        $index->refresh();

        //use the terms lookup feature to query for some data
        $termsFilter = new Terms();
        $termsFilter->setLookup('lastName', $type2, 'led zeppelin', 'members');
        $query = new \Elastica\Query();
        $query->setFilter($termsFilter);
        $results = $index->search($query);

        $this->assertEquals($results->count(), 4);
        $index->delete();
    }
}
