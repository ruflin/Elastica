<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';


class Elastica_QueryTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}


	public function testStringConversion() {
		$queryString = '{
			"query" : {
				"filtered" : {
				"filter" : {
					"range" : {
					"due" : {
						"gte" : "2011-07-18 00:00:00",
						"lt" : "2011-07-25 00:00:00"
					}
					}
				},
				"query" : {
					"text_phrase" : {
					"title" : "Call back request"
					}
				}
				}
			},
			"sort" : {
				"due" : {
				"reverse" : true
				}
			},
			"fields" : [
				"created", "assigned_to"
			]
			}';

		$query = new Elastica_Query_Builder($queryString);
		$queryArray = $query->toArray();

		$this->assertInternalType('array', $queryArray);

		$this->assertEquals('2011-07-18 00:00:00', $queryArray['query']['filtered']['filter']['range']['due']['gte']);
	}

	public function testRawQuery() {

		$textQuery = new Elastica_Query_Text();
		$textQuery->setField('title', 'test');

		$query1 = Elastica_Query::create($textQuery);

		$query2 = new Elastica_Query();
		$query2->setRawQuery(array('query' => array('text' => array('title' => 'test'))));

		$this->assertEquals($query1->toArray(), $query2->toArray());
	}

	public function testSetSort() {
		$client = new Elastica_Client();
		$index = $client->getIndex('test');
		$index->create(array(), true);
		$type = $index->getType('test');

		$doc = new Elastica_Document(1, array('name' => 'hello world'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(2, array('firstname' => 'guschti', 'lastname' => 'ruflin'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(3, array('firstname' => 'nicolas', 'lastname' => 'ruflin'));
		$type->addDocument($doc);


		$queryTerm = new Elastica_Query_Term();
		$queryTerm->addTerm('lastname', 'ruflin');

		$index->refresh();

		$query = Elastica_Query::create($queryTerm);

		// ASC order
		$query->setSort(array(array('firstname' => array('order' => 'asc'))));
		$resultSet = $type->search($query);
		$this->assertEquals(2, $resultSet->count());

		$first = $resultSet->current()->getData();
		$second = $resultSet->next()->getData();

		$this->assertEquals('guschti', $first['firstname']);
		$this->assertEquals('nicolas', $second['firstname']);


		// DESC order
		$query->setSort(array('firstname' => array('order' => 'desc')));
		$resultSet = $type->search($query);
		$this->assertEquals(2, $resultSet->count());

		$first = $resultSet->current()->getData();
		$second = $resultSet->next()->getData();

		$this->assertEquals('nicolas', $first['firstname']);
		$this->assertEquals('guschti', $second['firstname']);
	}

	public function testAddSort() {
		$query = new Elastica_Query();
		$sortParam = array('firstanem' => array('order' => 'asc'));
		$query->addSort($sortParam);

		$this->assertEquals($query->getParam('sort'), array($sortParam));
	}

	public function testSetRawQuery() {
		$query = new Elastica_Query();

		$params = array('query' => 'test');
		$query->setRawQuery($params);

		$this->assertEquals($params, $query->toArray());
	}

	public function testSetFields() {
		$query = new Elastica_Query();

		$params = array('query' => 'test');

		$query->setFields(array('firstname', 'lastname'));


		$data = $query->toArray();

		$this->assertContains('firstname', $data['fields']);
		$this->assertContains('lastname', $data['fields']);
		$this->assertEquals(2, count($data['fields']));
	}

	public function testGetQuery() {
		$query = new Elastica_Query();

		try {
			$query->getQuery();
			$this->fail('should throw exception because query does not exist');
		} catch(Elastica_Exception_Invalid $e) {
			$this->assertTrue(true);
		}


		$termQuery = new Elastica_Query_Term();
		$termQuery->addTerm('text', 'value');
		$query->setQuery($termQuery);

		$this->assertEquals($termQuery->toArray(), $query->getQuery());
	}
}
