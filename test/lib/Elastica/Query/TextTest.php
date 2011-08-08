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
}
