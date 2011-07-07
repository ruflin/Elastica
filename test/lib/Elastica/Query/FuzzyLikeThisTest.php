<?php
require_once dirname(__FILE__) . '/../../../bootstrap.php';

class Elastica_Query_FuzzyLikeThisTest extends PHPUnit_Framework_TestCase
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

		$type = new Elastica_Type($index, 'helloworldfuzzy');
        $mapping = new Elastica_Type_Mapping($type , array(
               'email' => array('store' => 'yes', 'type' => 'string', 'index' => 'analyzed'),
               'content' => array('store' => 'yes', 'type' => 'string',  'index' => 'analyzed'),
          ));

        $mapping->setSource(array('enabled' => false));
        $type->setMapping($mapping);
        

		$doc = new Elastica_Document(1000, array('email' => 'testemail@gmail.com', 'content' => 'This is a sample post. Hello World Fuzzy Like This!'));
		$type->addDocument($doc);

		// Refresh index
		$index->refresh();

		$fltQuery = new Elastica_Query_FuzzyLikeThis();
        $fltQuery->setLikeText("sample gmail");
        $fltQuery->addFields(array("email","content"));
        $fltQuery->setMinSimilarity(0.3);
        $fltQuery->setMaxQueryTerms(3);
		$resultSet = $type->search($fltQuery);
		$this->assertEquals(1, $resultSet->count());
	}
}
