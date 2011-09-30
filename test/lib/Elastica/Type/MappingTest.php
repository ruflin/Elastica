<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_MappingTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testMappingStoreFields() {
		$client = new Elastica_Client();
		$index = $client->getIndex('test');

		$index->create(array(), true);
		$type = $index->getType('test');

		$mapping = new Elastica_Type_Mapping($type,
			array(
				'firstname' => array('type' => 'string', 'store' => 'yes'),
				// default is store => no expected
				'lastname' => array('type' => 'string'),
			)
		);
		$mapping->disableSource();

		$type->setMapping($mapping);

		$firstname = 'Nicolas';
		$doc = new Elastica_Document(1,
			array(
				'firstname' => $firstname,
				'lastname' => 'Ruflin'
			)
		);

		$type->addDocument($doc);

		$index->refresh();
		$queryString = new Elastica_Query_QueryString('ruflin');
		$query = Elastica_Query::create($queryString);
		$query->setFields(array('*'));

		$resultSet = $type->search($query);
		$result = $resultSet->current();
		$fields = $result->getFields();

		$this->assertEquals($firstname, $fields['firstname']);
		$this->assertArrayNotHasKey('lastname', $fields);
		$this->assertEquals(1, count($fields));

		$index->flush();
		$document = $type->getDocument(1);

		$this->assertEmpty($document->getData());
	}

	public function testNestedMapping() {
		$client = new Elastica_Client();
		$index = $client->getIndex('test');

		$index->create(array(), true);
		$type = $index->getType('test');

		$this->markTestIncomplete('nested mapping is not set right yet');
		$mapping = new Elastica_Type_Mapping($type,
			array(
				'test' => array(
					'type' => 'object', 'store' => 'yes', 'properties' => array(
						'user' => array(
							'properties' => array(
								'firstname' => array('type' => 'string', 'store' => 'yes'),
								'lastname' => array('type' => 'string', 'store' => 'yes'),
								'age' => array('type' => 'integer', 'store' => 'yes'),
							)
						),
					),
				),
			)
		);

		$type->setMapping($mapping);

		$doc = new Elastica_Document(1, array(
			'user' => array(
				'firstname' => 'Nicolas',
				'lastname' => 'Ruflin',
				'age' => 9
			),
		));

		print_r($type->getMapping());
		exit();
		$type->addDocument($doc);

		$index->refresh();
		$resultSet = $type->search('ruflin');
		print_r($resultSet);
	}
}



