<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Filter_TermsTest extends Elastica_Test
{

	public function testToArray() {
		$query = new Elastica_Filter_Term();
		$key = 'name';
		$value = 'ruflin';
		$boost = 3;
		$query->setTerm($key, $value, $boost);
		
		$data = $query->toArray();
		
		$this->assertInternalType('array', $data['term']);
		$this->assertEquals(array($key => $value), $data['term']);
	}
}