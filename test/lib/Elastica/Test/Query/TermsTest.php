<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\Terms;
use Elastica\Test\Base as BaseTest;

class TermsTest extends BaseTest
{
    public function testFilteredSearch()
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('helloworld');

        $doc = new Document(1, array('name' => 'hello world'));
        $type->addDocument($doc);
        $doc = new Document(2, array('name' => 'nicolas ruflin'));
        $type->addDocument($doc);
        $doc = new Document(3, array('name' => 'ruflin'));
        $type->addDocument($doc);

        $query = new Terms();
        $query->setTerms('name', array('nicolas', 'hello'));

        $index->refresh();

        $resultSet = $type->search($query);

        $this->assertEquals(2, $resultSet->count());

        $query->addTerm('ruflin');
        $resultSet = $type->search($query);

        $this->assertEquals(3, $resultSet->count());
    }

    public function testSetMinimum()
    {
        $key = 'name';
        $terms = array('nicolas', 'ruflin');
        $minimum = 2;

        $query = new Terms($key, $terms);
        $query->setMinimumMatch($minimum);

        $data = $query->toArray();
        $this->assertEquals($minimum, $data['terms']['minimum_match']);
    }

    /**
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testInvalidParams()
    {
        $query = new Terms();

        $query->toArray();
    }
}
