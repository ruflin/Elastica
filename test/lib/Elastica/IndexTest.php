<?php

require_once dirname(__FILE__) . '/../../bootstrap.php';


class Elastica_IndexTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {

	}

	public function tearDown() {
	}

	public function testTest() {
		/*$client = new Elastica_Client();

		$index = $client->getIndex('aaa');
		$index->delete();
		$index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 1)));

		$doc = new Elastica_Document('user', 1, array('id' => 1, 'email' => 'test@test.com', 'username' => 'hanswurst', 'test' => array('2', '3', '5')));

		$index->addDocument($doc);
		$index->optimize();


		$index->setMapping('user',
			array(
				'id' => array('type' => 'integer', 'store' => 'yes'),
				'email' => array('type' => 'string', 'store' => 'no'),
				'username' => array('type' => 'string', 'store' => 'no'),
			)
		);

		print_r($index->getMapping('user'));*/

		//print_r($index->search('user', array('query' => 'hanswurst')));

	}

	public function testAddPDFFile() {
		$this->markTestIncomplete();
		$indexMapping = array(
			'file' => array('type' => 'attachment'),
			'text' => array('type' => 'string', 'store' => 'no'),
		);

		$indexParams = array(
			'index' => array(
				'number_of_shards' => 1,
				'number_of_replicas' => 1
			),
		);

		$client = new Elastica_Client();
		$index = new Elastica_Index($client, 'content');
		$type = new Elastica_Type($index, 'content');

		$index->create($indexParams, true);
		$type->setMapping($indexMapping);

		$doc1 = new Elastica_Document(1);
		$doc1->addFile('file', BASE_PATH . '/data/test.pdf');
		$doc1->add('text', 'basel world');
		$type->addDocument($doc1);

		$doc2 = new Elastica_Document(2);
		$doc2->add('text', 'running in basel');
		$type->addDocument($doc2);

		$index->optimize();

		$resultSet = $type->search('xodoa');
		$this->assertEquals(1, $resultSet->count());

		$resultSet = $type->search('basel');
		$this->assertEquals(2, $resultSet->count());

		$resultSet = $type->search('ruflin');
		$this->assertEquals(0, $resultSet->count());
	}

	public function testAddWordxFile() {
		$this->markTestIncomplete();
		$indexMapping = array(
			'file' => array('type' => 'attachment'),
			'text' => array('type' => 'string', 'store' => 'no'),
		);

		$indexParams = array(
			'index' => array(
				'number_of_shards' => 1,
				'number_of_replicas' => 1
			),
		);

		$client = new Elastica_Client();
		$index = new Elastica_Index($client, 'content');
		$type = new Elastica_Type($index, 'content');

		$index->create($indexParams, true);
		$type->setMapping($indexMapping);

		$doc1 = new Elastica_Document(1);
		$doc1->addFile('file', BASE_PATH . '/data/test.docx');
		$doc1->add('text', 'basel world');
		$type->addDocument($doc1);

		$doc2 = new Elastica_Document(2);
		$doc2->add('text', 'running in basel');
		$type->addDocument($doc2);

		$index->optimize();

		$resultSet = $type->search('xodoa');
		$this->assertEquals(1, $resultSet->count());

		$resultSet = $type->search('basel');
		$this->assertEquals(2, $resultSet->count());

		$resultSet = $type->search('ruflin');
		$this->assertEquals(0, $resultSet->count());
	}

	public function testAddRemoveAlias() {
		$client = new Elastica_Client();

		$indexName1 = 'test1';
		$aliasName = 'test-alias';
		$typeName = 'test';

		$index = $client->getIndex($indexName1);
		$index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 1)), true);

		$doc = new Elastica_Document(1, array('id' => 1, 'email' => 'test@test.com', 'username' => 'ruflin'));

		$type = $index->getType($typeName);
		$type->addDocument($doc);
		$index->refresh();

		$resultSet = $type->search('ruflin');

		$this->assertEquals(1, $resultSet->count());

		$data = $index->addAlias($aliasName, true)->getData();
		$this->assertTrue($data['ok']);


		$index2 = $client->getIndex($aliasName);
		$type2 = $index2->getType($typeName);

		$resultSet2 = $type2->search('ruflin');
		$this->assertEquals(1, $resultSet2->count());

		$response = $index->removeAlias($aliasName)->getData();
		$this->assertTrue($response['ok']);

		try {
			$client->getIndex($aliasName)->getType($typeName)->search('ruflin');
			$this->fail('Should throw no index exception');
		} catch (Elastica_Exception_Response $e) {
			$this->assertTrue(true);
		}
	}

	public function testDeleteIndexDeleteAlias() {
		$indexName = 'test';
		$aliasName = 'test-aliase';

		$client = new Elastica_Client();
		$index = $client->getIndex($indexName);

		$index->create(array(), true);
		$index->addAlias($aliasName);

		$status = new Elastica_Status($client);
		$this->assertTrue($status->indexExists($indexName));
		$this->assertTrue($status->aliasExists($aliasName));

		// Deleting index should also remove alias
		$index->delete();

		$status->refresh();
		$this->assertFalse($status->indexExists($indexName));
		$this->assertFalse($status->aliasExists($aliasName));
	}

	public function testAddAliasTwoIndices() {
		$indexName1 = 'test1';
		$indexName2 = 'test2';
		$aliasName = 'test-alias';

		$client = new Elastica_Client();
		$index1 = $client->getIndex($indexName1);
		$index2 = $client->getIndex($indexName2);

		$index1->create(array(), true);
		$index1->addAlias($aliasName);
		$index2->create(array(), true);

		$status = new Elastica_Status($client);
		$this->assertTrue($status->indexExists($indexName1));
		$this->assertTrue($status->indexExists($indexName2));
		$this->assertTrue($status->aliasExists($aliasName));
		$this->assertTrue($index1->getStatus()->hasAlias($aliasName));
		$this->assertFalse($index2->getStatus()->hasAlias($aliasName));

		$index2->addAlias($aliasName);
		$this->assertTrue($index1->getStatus()->hasAlias($aliasName));
		$this->assertTrue($index2->getStatus()->hasAlias($aliasName));
	}

	public function testReplaceAlias() {
		$indexName1 = 'test1';
		$indexName2 = 'test2';
		$aliasName = 'test-alias';

		$client = new Elastica_Client();
		$index1 = $client->getIndex($indexName1);
		$index2 = $client->getIndex($indexName2);

		$index1->create(array(), true);
		$index1->addAlias($aliasName);
		$index2->create(array(), true);

		$status = new Elastica_Status($client);
		$this->assertTrue($status->indexExists($indexName1));
		$this->assertTrue($status->indexExists($indexName2));
		$this->assertTrue($status->aliasExists($aliasName));
		$this->assertTrue($index1->getStatus()->hasAlias($aliasName));
		$this->assertFalse($index2->getStatus()->hasAlias($aliasName));

		$index2->addAlias($aliasName, true);
		$this->assertFalse($index1->getStatus()->hasAlias($aliasName));
		$this->assertTrue($index2->getStatus()->hasAlias($aliasName));
	}
}
