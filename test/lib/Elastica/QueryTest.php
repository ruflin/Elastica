<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';


class Elastica_QueryTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}


	public function testRawQuery() {
		$query = '{
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

		$this->markTestIncomplete('Implement test for query builder');
		//print_r(json_decode($query, true));
	}
}
