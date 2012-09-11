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

	public function testMatchAllIndicesTypes() {
		$index1 = $this->_createIndex('test1');
		$index2 = $this->_createIndex('test1');

		$doc = new Elastica_Document(1, array('name' => 'ruflin'));
		$index1->getType('test')->addDocument($doc);
		$index2->getType('test')->addDocument($doc);

		$index1->refresh();
		$index2->refresh();

		$search = new Elastica_Search($index1->getClient());
		$resultSet = $search->search(new Elastica_Query_MatchAll());

		$this->assertEquals(2, $resultSet->count());
	}
}
