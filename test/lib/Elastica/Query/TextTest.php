<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_WildcardTest extends PHPUnit_Framework_TestCase
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

		$query = new Elastica_Query_Text($queryText);
		$query->setType($type);
		$query->setMessageParam('analyzer', $analyzer);
		$query->setMaxExpansions($maxExpansions);

		$expectedArray = array(
			'text' => array(
				'message' => array(
					'query' => $queryText,
					'type' => $type,
					'analyzer' => $analyzer,
					'max_expansions' => $maxExpansions,
				)
			)
		);

		$this->assertEquals($expectedArray, $query->toArray());
	}
}
