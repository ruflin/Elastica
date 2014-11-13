<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Filter\Term;
use Elastica\Query\Filtered;
use Elastica\Query\QueryString;
use Elastica\Test\Base as BaseTest;

class FilteredTest extends BaseTest
{
    public function testFilteredSearch()
    {
        $index = $this->_createIndex();
        $type = $index->getType('helloworld');

        $doc = new Document(1, array('id' => 1, 'email' => 'test@test.com', 'username' => 'hanswurst', 'test' => array('2', '3', '5')));
        $type->addDocument($doc);
        $doc = new Document(2, array('id' => 2, 'email' => 'test@test.com', 'username' => 'peter', 'test' => array('2', '3', '5')));
        $type->addDocument($doc);

        $queryString = new QueryString('test*');

        $filter1 = new Term();
        $filter1->setTerm('username', 'peter');

        $filter2 = new Term();
        $filter2->setTerm('username', 'qwerqwer');

        $query1 = new Filtered($queryString, $filter1);
        $query2 = new Filtered($queryString, $filter2);
        $index->refresh();

        $resultSet = $type->search($queryString);
        $this->assertEquals(2, $resultSet->count());

        $resultSet = $type->search($query1);
        $this->assertEquals(1, $resultSet->count());

        $resultSet = $type->search($query2);
        $this->assertEquals(0, $resultSet->count());
    }

    public function testFilteredGetter()
    {
        $queryString = new QueryString('test*');

        $filter1 = new Term();
        $filter1->setTerm('username', 'peter');

        $filter2 = new Term();
        $filter2->setTerm('username', 'qwerqwer');

        $query1 = new Filtered($queryString, $filter1);
        $query2 = new Filtered($queryString, $filter2);

        $this->assertEquals($query1->getQuery(), $queryString);
        $this->assertEquals($query2->getQuery(), $queryString);
        $this->assertEquals($query1->getFilter(), $filter1);
        $this->assertEquals($query2->getFilter(), $filter2);
    }

    /**
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testFilteredWithoutArgumentsShouldRaiseException()
    {
        $query = new Filtered();
        $query->toArray();
    }
    
    public function testFilteredSearchNoQuery()
    {
        $index = $this->_createIndex();
        $type = $index->getType('helloworld');

        $doc = new Document(1, array('id' => 1, 'email' => 'test@test.com', 'username' => 'hanswurst', 'test' => array('2', '3', '5')));
        $type->addDocument($doc);
        $doc = new Document(2, array('id' => 2, 'email' => 'test@test.com', 'username' => 'peter', 'test' => array('2', '3', '5')));
        $type->addDocument($doc);

        $filter = new Term();
        $filter->setTerm('username', 'peter');

        $query = new Filtered(null, $filter);
        $index->refresh();

        $resultSet = $type->search($query);
        $this->assertEquals(1, $resultSet->count());
    }
    
    public function testFilteredSearchNoFilter()
    {
        $index = $this->_createIndex();
        $type = $index->getType('helloworld');

        $doc = new Document(1, array('id' => 1, 'email' => 'test@test.com', 'username' => 'hanswurst', 'test' => array('2', '3', '5')));
        $type->addDocument($doc);
        $doc = new Document(2, array('id' => 2, 'email' => 'test@test.com', 'username' => 'peter', 'test' => array('2', '3', '5')));
        $type->addDocument($doc);

        $queryString = new QueryString('hans*');

        $query = new Filtered($queryString);
        $index->refresh();

        $resultSet = $type->search($query);
        $this->assertEquals(1, $resultSet->count());
    }
    
}
