<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_TextTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testToArray() {
		$queryText = 'Nicolas Ruflin';
		$type = 'text_phrase';
		$analyzer = 'myanalyzer';
		$maxExpansions = 12;
		$field = 'test';

		$query = new Elastica_Query_Text();
		$query->setFieldQuery($field, $queryText);
		$query->setFieldType($field, $type);
		$query->setFieldParam($field, 'analyzer', $analyzer);
		$query->setFieldMaxExpansions($field, $maxExpansions);

		$expectedArray = array(
			'text' => array(
				$field => array(
					'query' => $queryText,
					'type' => $type,
					'analyzer' => $analyzer,
					'max_expansions' => $maxExpansions,
				)
			)
		);

		$this->assertEquals($expectedArray, $query->toArray());
	}

	public function testTextPhrase() {

		$client = new Elastica_Client();
		$index = $client->getIndex('test');
		$index->create(array(), true);
		$type = $index->getType('test');

		$doc = new Elastica_Document(1, array('name' => 'Basel-Stadt'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(2, array('name' => 'New York'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(3, array('name' => 'New Hampshire'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(4, array('name' => 'Basel Land'));
		$type->addDocument($doc);


		$index->refresh();

		$type = 'text_phrase';
		$field = 'name';

		$query = new Elastica_Query_Text();
		$query->setFieldQuery($field, 'Basel New');
		$query->setField('operator', 'OR');
		$query->setFieldType($field, $type);

		$resultSet = $index->search($query);

		$this->assertEquals(4, $resultSet->count());
	}
}
