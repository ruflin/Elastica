<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';


class Elastica_PercolatorTest extends Elastica_Test
{
	public function testConstruct() {
		$index = $this->_createIndex();
		$percolator = new Elastica_Percolator($index);
		
		$percolatorName = 'percotest';
		
		$query = new Elastica_Query_Term(array('field1' => 'value1'));
		$response = $percolator->registerQuery($percolatorName, $query);
		
		$data = $response->getData();
		
		$expectedArray = array(
			'ok' => true,
			'_type' => $index->getName(),
			'_index' => '_percolator',
			'_id' => $percolatorName,
			'_version' => 1
		);

		$this->assertEquals($expectedArray, $data);
	}
	
	public function testMatchDoc() {
		$index = $this->_createIndex();
		$percolator = new Elastica_Percolator($index);
		
		$percolatorName = 'percotest';
		
		$query = new Elastica_Query_Term(array('name' => 'ruflin'));
		$response = $percolator->registerQuery($percolatorName, $query);

		$this->assertTrue($response->isOk());
		$this->assertFalse($response->hasError());

		$doc1 = new Elastica_Document();
		$doc1->add('name', 'ruflin');

		$doc2 = new Elastica_Document();
		$doc2->add('name', 'nicolas');

		$index = new Elastica_Index($index->getClient(), '_percolator');
		$index->refresh();
		
		$matches1 = $percolator->matchDoc($doc1);
		
		$this->assertTrue(in_array($percolatorName, $matches1));
		$this->assertEquals(1, count($matches1));
		
		$matches2 = $percolator->matchDoc($doc2);
		$this->assertEmpty($matches2);
	}
}