<?php
require_once dirname(__FILE__) . '/../../bootstrap.php';


class Elastica_DocumentTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}
	
	public function testAdd() {
		$doc = new Elastica_Document();
		$returnValue = $doc->add('key', 'value');
		$data = $doc->getData();
		$this->assertArrayHasKey('key', $data);
		$this->assertEquals('value', $data['key']);
		$this->assertInstanceOf('Elastica_Document', $returnValue);
	}
	
	public function testAddFile()
	{
		$doc = new Elastica_Document();
		$returnValue = $doc->addFile('key', '/dev/null');
		$this->assertInstanceOf('Elastica_Document', $returnValue);
	}
	
	public function testAddGeoPoint()
	{
		$doc = new Elastica_Document();
		$returnValue = $doc->addGeoPoint('point', 38.89859, -77.035971);
		$this->assertInstanceOf('Elastica_Document', $returnValue);
	}
	
	public function testSetData()
	{
		$doc = new Elastica_Document();
		$returnValue = $doc->setData(array('data'));
		$this->assertInstanceOf('Elastica_Document', $returnValue);
	}
}