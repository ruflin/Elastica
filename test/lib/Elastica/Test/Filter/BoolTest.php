<?php

namespace Elastica\Test\Filter;

use \Elastica\Query;
use Elastica\Filter\Bool;
use Elastica\Filter\Term;
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

    public function testBoolFilter()
    {
        $index = $this->_createIndex('bool_filter_test');
        $type = $index->getType('book');

        //index some test data
        $type->addDocument(new \Elastica\Document(1, array('author' => 'Michael Shermer', 'title' => 'The Believing Brain', 'publisher' => 'Robinson')));
        $type->addDocument(new \Elastica\Document(2, array('author' => 'Jared Diamond', 'title' => 'Guns, Germs and Steel', 'publisher' => 'Vintage')));
        $type->addDocument(new \Elastica\Document(3, array('author' => 'Jared Diamond', 'title' => 'Collapse', 'publisher' => 'Penguin')));
        $type->addDocument(new \Elastica\Document(4, array('author' => 'Richard Dawkins', 'title' => 'The Selfish Gene', 'publisher' => 'OUP Oxford')));
        $type->addDocument(new \Elastica\Document(5, array('author' => 'Anthony Burges', 'title' => 'A Clockwork Orange', 'publisher' => 'Penguin')));

        $index->refresh();

        //use the terms lookup feature to query for some data
        //build query
        //must
        //  should
        //      author = jared
        //      author = richard
        //  must_not
        //      publisher = penguin

        //construct the query
        $query = new Query();
        $mainBoolFilter = new Bool();
        $shouldFilter = new Bool();
        $authorFilter1 = new Term();
        $authorFilter1->setTerm('author', 'jared');
        $authorFilter2 = new Term();
        $authorFilter2->setTerm('author', 'richard');
        $shouldFilter->addShould(array($authorFilter1, $authorFilter2));

        $mustNotFilter = new Bool();
        $publisherFilter = new Term();
        $publisherFilter->setTerm('publisher', 'penguin');
        $mustNotFilter->addMustNot($publisherFilter);

        $mainBoolFilter->addMust(array($shouldFilter, $mustNotFilter));
        $query->setPostFilter($mainBoolFilter);
        //execute the query
        $results = $index->search($query);

        //check the number of results
        $this->assertEquals($results->count(), 2, 'Bool filter with child Bool filters: number of results check');

        //count compare the id's
        $ids = array();
        /** @var \Elastica\Result $result **/
        foreach($results as $result){
            $ids[] = $result->getId();
        }
        $this->assertEquals($ids, array("2","4"), 'Bool filter with child Bool filters: result ID check');

        $index->delete();
    }
}
