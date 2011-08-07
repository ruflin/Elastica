<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_MoreLikeThisTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
	}

	public function tearDown() {
	}

	public function testSearch() {

		$client = new Elastica_Client();
		$index = new Elastica_Index($client, 'test');
		$index->create(array(), true);
		$index->getSettings()->setNumberOfReplicas(0);
		//$index->getSettings()->setNumberOfShards(1);

		$type = new Elastica_Type($index, 'helloworldmlt');
		$mapping = new Elastica_Type_Mapping($type , array(
			'email' => array('store' => 'yes', 'type' => 'string', 'index' => 'analyzed'),
			'content' => array('store' => 'yes', 'type' => 'string',  'index' => 'analyzed'),
		));

		$mapping->setSource(array('enabled' => false));
		$type->setMapping($mapping);


		$doc = new Elastica_Document(1000, array('email' => 'testemail@gmail.com', 'content' => 'This is a sample post. Hello World Fuzzy Like This!'));
		$type->addDocument($doc);

		$doc = new Elastica_Document(1001, array('email' => 'nospam@gmail.com', 'content' => 'This is a fake nospam email address for gmail'));
		$type->addDocument($doc);

		// Refresh index
		$index->refresh();

		$mltQuery = new Elastica_Query_MoreLikeThis();
		$mltQuery->setLikeText('fake gmail sample');
		$mltQuery->setFields(array('email','content'));
		$mltQuery->setMaxQueryTerms(1);
		$mltQuery->setMinDocFrequency(1);
		$mltQuery->setMinTermFrequency(1);

		$query = new Elastica_Query();
		$query->setFields(array('email', 'content'));
		$query->setQuery($mltQuery);

		$resultSet = $type->search($query);
		$resultSet->getResponse()->getData();
		$this->assertEquals(2, $resultSet->count());
	}
}
