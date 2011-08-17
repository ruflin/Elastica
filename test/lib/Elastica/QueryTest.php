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
		$client = new Elastica_Client();
		$index = $client->getIndex('test');
		$index->create(array(), true);
		$type = $index->getType('test');

		$textQuery = new Elastica_Query_Text();
		$textQuery->setField('title', 'test');

		$query1 = Elastica_Query::create($textQuery);

		$query2 = new Elastica_Query();
		$query2->setRawQuery(array('query' => array('text' => array('title' => 'test'))));

		$this->assertEquals($query1->toArray(), $query2->toArray());
	}
}
