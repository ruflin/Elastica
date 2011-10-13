<?php

require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Filter_PrefixTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}
	
	public function testToArray() {
		$field = 'name';
		$prefix = 'ruf';
		
		$filter = new Elastica_Filter_Prefix($field, $prefix);
				
		$expectedArray = array(
			'prefix' => array(
				$field => $prefix
			)
		);
		
		$this->assertequals($expectedArray, $filter->toArray());
	}
	
	public function testDifferentPrefixes() {
		$client = new Elastica_Client();
		$index = $client->getIndex('test');

		/*$indexParams = array(
			'analysis' => array(
				'analyzer' => array(
					'lw' => array(
						'type' => 'custom',
						'tokenizer' => 'keyword',
						'filter' => array('lowercase')
					)
				),
			)
		);*/

		$index->create(array(), true);
		$type = $index->getType('test');

		$mapping = new Elastica_Type_Mapping($type, array(
				'name' => array('type' => 'string', 'store' => 'no', 'index' => 'not_analyzed'),
			)
		);
		$type->setMapping($mapping);

		$doc = new Elastica_Document(1, array('name' => 'Basel-Stadt'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(2, array('name' => 'New York'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(3, array('name' => 'Baden'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(4, array('name' => 'Baden Baden'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(5, array('name' => 'New Orleans'));
		$type->addDocument($doc);

		$index->refresh();


		$query = new Elastica_Filter_Prefix('name', 'Ba');
		$resultSet = $index->search($query);
		$this->assertEquals(3, $resultSet->count());
		
		// Lower case should not return a result
		$query = new Elastica_Filter_Prefix('name', 'ba');
		$resultSet = $index->search($query);
		$this->assertEquals(0, $resultSet->count());		

		$query = new Elastica_Filter_Prefix('name', 'Baden');
		$resultSet = $index->search($query);
		$this->assertEquals(2, $resultSet->count());
		
		$query = new Elastica_Filter_Prefix('name', 'Baden B');
		$resultSet = $index->search($query);
		$this->assertEquals(1, $resultSet->count());


		$query = new Elastica_Filter_Prefix('name', 'Baden Bas');
		$resultSet = $index->search($query);
		$this->assertEquals(0, $resultSet->count());
	}
	
	public function testDifferentPrefixesLowercase() {
		$client = new Elastica_Client();
		$index = $client->getIndex('test');
	
		$indexParams = array(
			'analysis' => array(
				'analyzer' => array(
					'lw' => array(
						'type' => 'custom',
						'tokenizer' => 'keyword',
						'filter' => array('lowercase')
					)
				),
			)
		);
	
		$index->create($indexParams, true);
		$type = $index->getType('test');
	
		$mapping = new Elastica_Type_Mapping($type, array(
				'name' => array('type' => 'string', 'store' => 'no', 'analyzer' => 'lw'),
			)
		);
		$type->setMapping($mapping);
	
		$doc = new Elastica_Document(1, array('name' => 'Basel-Stadt'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(2, array('name' => 'New York'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(3, array('name' => 'Baden'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(4, array('name' => 'Baden Baden'));
		$type->addDocument($doc);
		$doc = new Elastica_Document(5, array('name' => 'New Orleans'));
		$type->addDocument($doc);
	
		$index->refresh();
	
	
		$query = new Elastica_Filter_Prefix('name', 'ba');
		$resultSet = $index->search($query);
		$this->assertEquals(3, $resultSet->count());
	
		// Upper case should not return a result
		$query = new Elastica_Filter_Prefix('name', 'Ba');
		$resultSet = $index->search($query);
		$this->assertEquals(0, $resultSet->count());
	
		$query = new Elastica_Filter_Prefix('name', 'baden');
		$resultSet = $index->search($query);
		$this->assertEquals(2, $resultSet->count());
	
		$query = new Elastica_Filter_Prefix('name', 'baden b');
		$resultSet = $index->search($query);
		$this->assertEquals(1, $resultSet->count());
	
	
		$query = new Elastica_Filter_Prefix('name', 'baden bas');
		$resultSet = $index->search($query);
		$this->assertEquals(0, $resultSet->count());
	}
}