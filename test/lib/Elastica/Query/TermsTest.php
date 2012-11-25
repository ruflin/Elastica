<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_TermsTest extends PHPUnit_Framework_TestCase
{
    public function testFilteredSearch()
    {
        $client = new Elastica_Client();
        $index = $client->getIndex('test');
        $index->create(array(), true);
        $type = $index->getType('helloworld');

        $doc = new Elastica_Document(1, array('name' => 'hello world'));
        $type->addDocument($doc);
        $doc = new Elastica_Document(2, array('name' => 'nicolas ruflin'));
        $type->addDocument($doc);
        $doc = new Elastica_Document(3, array('name' => 'ruflin'));
        $type->addDocument($doc);

        $query = new Elastica_Query_Terms();
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

        $query = new Elastica_Query_Terms($key, $terms);
        $query->setMinimumMatch($minimum);

        $data = $query->toArray();
        $this->assertEquals($minimum, $data['terms']['minimum_match']);
    }

    /**
     * @expectedException Elastica_Exception_Invalid
     */
    public function testInvalidParams()
    {
        $query = new Elastica_Query_Terms();

        $query->toArray();
    }
}
