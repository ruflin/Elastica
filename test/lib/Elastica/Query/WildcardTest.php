<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_WildcardTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testConstructEmpty() {
		$wildcard = new Elastica_Query_Wildcard();
		$this->assertEmpty($wildcard->getParams());
	}

	public function testToArray() {
		$key = 'name';
		$value = 'Ru*lin';
		$boost = 2.0;

		$wildcard = new Elastica_Query_Wildcard($key, $value, $boost);

		$expectedArray = array(
			'wildcard' => array(
				$key => array(
					'value' => $value,
					'boost' => $boost
				)
			)
		);

		$this->assertEquals($expectedArray, $wildcard->toArray());
	}

	public function testSearchWithAnalyzer() {
		$client = new Elastica_Client();
		$index = $client->getIndex('test');

		$indexParams = array(
			'analysis' => array(
				'analyzer' => array(
					'lw' => array(
						'type' => 'custom',
						'tokenizer' => 'keyword',
						'filter' => array('lowercase')
					)
				),
			)
		);

		$index->create($indexParams, true);
		$type = $index->getType('test');

		$mapping = new Elastica_Type_Mapping($type, array(
				'name' => array('type' => 'string', 'store' => 'no', 'analyzer' => 'lw'),
			)
		);
		$type->setMapping($mapping);

		$doc = new Elastica_Document(1, array('name' => 'Basel-Stadt'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(2, array('name' => 'New York'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(3, array('name' => 'Baden'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(4, array('name' => 'Baden Baden'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(5, array('name' => 'New Orleans'));
		$type->addDocument($doc);

		$index->refresh();


		$query = new Elastica_Query_Wildcard();
		$query->setValue('name', 'ba*');
		$resultSet = $index->search($query);

		$this->assertEquals(3, $resultSet->count());


		$query = new Elastica_Query_Wildcard();
		$query->setValue('name', 'baden*');
		$resultSet = $index->search($query);

		$this->assertEquals(2, $resultSet->count());


		$query = new Elastica_Query_Wildcard();
		$query->setValue('name', 'baden b*');
		$resultSet = $index->search($query);

		$this->assertEquals(1, $resultSet->count());


		$query = new Elastica_Query_Wildcard();
		$query->setValue('name', 'baden bas*');
		$resultSet = $index->search($query);

		$this->assertEquals(0, $resultSet->count());
	}
}
