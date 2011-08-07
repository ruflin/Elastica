<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_MatchAllTest extends PHPUnit_Framework_TestCase
{
	public function testToArray() {
		$query = new Elastica_Query_MatchAll();

		$expectedArray = array('match_all' => new stdClass());

		$this->assertEquals($expectedArray, $query->toArray());
	}
}