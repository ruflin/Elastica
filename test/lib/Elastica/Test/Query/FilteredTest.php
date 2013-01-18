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
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
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
}
