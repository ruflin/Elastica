<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\Filtered;
use Elastica\Query\QueryString;
use Elastica\Query\Term;
use Elastica\Test\DeprecatedClassBase as BaseTest;

class FilteredTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testFilteredSearch()
    {
        $index = $this->_createIndex();
        $type = $index->getType('helloworld');

        $type->addDocuments([
            new Document(1, ['id' => 1, 'email' => 'test@test.com', 'username' => 'hanswurst', 'test' => ['2', '3', '5']]),
            new Document(2, ['id' => 2, 'email' => 'test@test.com', 'username' => 'peter', 'test' => ['2', '3', '5']]),
        ]);

        $index->refresh();

        $queryString = new QueryString('test*');

        $filter1 = new Term();
        $filter1->setTerm('username', 'peter');

        $filter2 = new Term();
        $filter2->setTerm('username', 'qwerqwer');

        $query1 = new Filtered($queryString, $filter1);
        $query2 = new Filtered($queryString, $filter2);

        $resultSet = $type->search($queryString);
        $this->assertEquals(2, $resultSet->count());

        $this->_markSkipped50('no [query] registered for [filtered]');
        $resultSet = $type->search($query1);
        $this->assertEquals(1, $resultSet->count());

        $resultSet = $type->search($query2);
        $this->assertEquals(0, $resultSet->count());
    }

    /**
     * @group unit
     */
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
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testFilteredWithoutArgumentsShouldRaiseException()
    {
        $query = new Filtered();
        $query->toArray();
    }

    /**
     * @group functional
     */
    public function testFilteredSearchNoQuery()
    {
        $index = $this->_createIndex();
        $type = $index->getType('helloworld');

        $type->addDocuments([
            new Document(1, ['id' => 1, 'email' => 'test@test.com', 'username' => 'hanswurst', 'test' => ['2', '3', '5']]),
            new Document(2, ['id' => 2, 'email' => 'test@test.com', 'username' => 'peter', 'test' => ['2', '3', '5']]),
        ]);

        $index->refresh();

        $filter = new Term();
        $filter->setTerm('username', 'peter');

        $query = new Filtered(null, $filter);

        $this->_markSkipped50('no [query] registered for [filtered]');
        $resultSet = $type->search($query);
        $this->assertEquals(1, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testFilteredSearchNoFilter()
    {
        $index = $this->_createIndex();
        $type = $index->getType('helloworld');

        $doc = new Document(1, ['id' => 1, 'email' => 'test@test.com', 'username' => 'hanswurst', 'test' => ['2', '3', '5']]);
        $type->addDocument($doc);
        $doc = new Document(2, ['id' => 2, 'email' => 'test@test.com', 'username' => 'peter', 'test' => ['2', '3', '5']]);
        $type->addDocument($doc);

        $index->refresh();

        $queryString = new QueryString('hans*');

        $query = new Filtered($queryString);

        $this->_markSkipped50('no [query] registered for [filtered]');
        $resultSet = $type->search($query);
        $this->assertEquals(1, $resultSet->count());
    }
}
