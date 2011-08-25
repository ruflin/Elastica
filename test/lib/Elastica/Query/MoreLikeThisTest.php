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
	
	public function testSetFields() {
		$query = new Elastica_Query_MoreLikeThis();
		
		$fields = array('firstname', 'lastname');
		$query->setFields($fields);
		
		$data = $query->toArray();
		$this->assertEquals($fields, $data['more_like_this']['mlt_fields']);		
	}
	
	public function testSetLikeText() {
		$query = new Elastica_Query_MoreLikeThis();
		$query->setLikeText(' hello world');
		
		$data = $query->toArray();
		$this->assertEquals('hello world', $data['more_like_this']['like_text']);
	}
	
	public function testSetBoost() {
		$query = new Elastica_Query_MoreLikeThis();
		
		$boost = 1.3;
		$query->setBoost($boost);
		
		$this->assertEquals($boost, $query->getParam('boost'));
	}
	
	public function testSetMaxQueryTerms() {
		$query = new Elastica_Query_MoreLikeThis();
		
		$max = 3;
		$query->setMaxQueryTerms($max);
		
		$this->assertEquals($max, $query->getParam('max_query_terms'));
	}
	
	public function testSetPercentTermsToMatch() {
		$query = new Elastica_Query_MoreLikeThis();
		
		$match = 0.8;
		$query->setPercentTermsToMatch($match);
		
		$this->assertEquals($match, $query->getParam('percent_terms_to_match'));
	}
	
	public function testSetMinDocFrequency() {
		$query = new Elastica_Query_MoreLikeThis();
		
		$freq = 2;
		$query->setMinDocFrequency($freq);
		
		$this->assertEquals($freq, $query->getParam('min_doc_freq'));
	}
	
	public function testSetMaxDocFrequency() {
		$query = new Elastica_Query_MoreLikeThis();
		
		$freq = 2;
		$query->setMaxDocFrequency($freq);
		
		$this->assertEquals($freq, $query->getParam('max_doc_freq'));
	}
	
	public function testSetMinWordLength() {
		$query = new Elastica_Query_MoreLikeThis();
		
		$length = 4;
		$query->setMinWordLength($length);
		
		$this->assertEquals($length, $query->getParam('min_word_length'));
	}
	
	public function testSetMaxWordLength() {
		$query = new Elastica_Query_MoreLikeThis();
		
		$length = 5;
		$query->setMaxWordLength($length);
		
		$this->assertEquals($length, $query->getParam('max_word_length'));
	}
	
	public function testSetBoostTerms() {
		$query = new Elastica_Query_MoreLikeThis();
		
		$boost = false;
		$query->setBoostTerms($boost);
		
		$this->assertEquals($boost, $query->getParam('boost_terms'));
	}
	
	public function testSetAnalyzer() {
		$query = new Elastica_Query_MoreLikeThis();
		
		$analyzer = 'UpperCase';
		$query->setAnalyzer($analyzer);
		
		$this->assertEquals($analyzer, $query->getParam('analyzer'));
	}
	
	public function testSetStopWords() {
		$query = new Elastica_Query_MoreLikeThis();

		$stopWords = array('no', 'yes', 'test');
		$query->setStopWords($stopWords);

		$this->assertEquals($stopWords, $query->getParam('stop_words'));
	}		
}
