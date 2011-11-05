<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_NestedTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testSetQuery() {
		$nested = new Elastica_Query_Nested();
		$path = 'test1';
		
		$queryString = new Elastica_Query_QueryString('test');
		$this->assertInstanceOf('Elastica_Query_Nested', $nested->setQuery($queryString));
		$this->assertInstanceOf('Elastica_Query_Nested', $nested->setPath($path));
		$expected = array(
			'nested' => array(
				'query' => $queryString->toArray(),
				'path' => $path,
			)
		);
		
		$this->assertEquals($expected, $nested->toArray());
	}
}
