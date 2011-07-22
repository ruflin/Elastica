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
}
