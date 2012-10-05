<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_MatchAllTest extends Elastica_Test
{
    public function testToArray()
    {
        $query = new Elastica_Query_MatchAll();

        $expectedArray = array('match_all' => new stdClass());

        $this->assertEquals($expectedArray, $query->toArray());
    }

    public function testMatchAllIndicesTypes()
    {
        $index1 = $this->_createIndex('test1');
        $index2 = $this->_createIndex('test2');

        $client = $index1->getClient();

        $search1 = new Elastica_Search($client);
        $resultSet1 = $search1->search(new Elastica_Query_MatchAll());

        $doc = new Elastica_Document(1, array('name' => 'ruflin'));
        $index1->getType('test')->addDocument($doc);
        $index2->getType('test')->addDocument($doc);

        $index1->refresh();
        $index2->refresh();

        $search2 = new Elastica_Search($client);
        $resultSet2 = $search2->search(new Elastica_Query_MatchAll());

        $this->assertEquals($resultSet1->getTotalHits() + 2, $resultSet2->getTotalHits());
    }
}
