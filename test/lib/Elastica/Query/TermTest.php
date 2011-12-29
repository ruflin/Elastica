<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_TermTest extends Elastica_Test
{

	public function testToArray() {
		$query = new Elastica_Query_Term();
		$key = 'name';
		$value = 'nicolas';
		$boost = 2;
		$query->setTerm($key, $value, $boost);
		
		$data = $query->toArray();
		
		$this->assertInternalType('array', $data['term']);
		$this->assertInternalType('array', $data['term'][$key]);
		$this->assertEquals($data['term'][$key]['value'], $value);
		$this->assertEquals($data['term'][$key]['boost'], $boost);
	}
}