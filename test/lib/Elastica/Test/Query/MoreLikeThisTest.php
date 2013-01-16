<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\MoreLikeThis;
use Elastica\Type;
use Elastica\Type\Mapping;
use Elastica\Test\Base as BaseTest;

class MoreLikeThisTest extends BaseTest
{
    public function testSearch()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test');
        $index->create(array(), true);
        $index->getSettings()->setNumberOfReplicas(0);
        //$index->getSettings()->setNumberOfShards(1);

        $type = new Type($index, 'helloworldmlt');
        $mapping = new Mapping($type , array(
            'email' => array('store' => 'yes', 'type' => 'string', 'index' => 'analyzed'),
            'content' => array('store' => 'yes', 'type' => 'string',  'index' => 'analyzed'),
        ));

        $mapping->setSource(array('enabled' => false));
        $type->setMapping($mapping);

        $doc = new Document(1000, array('email' => 'testemail@gmail.com', 'content' => 'This is a sample post. Hello World Fuzzy Like This!'));
        $type->addDocument($doc);

        $doc = new Document(1001, array('email' => 'nospam@gmail.com', 'content' => 'This is a fake nospam email address for gmail'));
        $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $mltQuery = new MoreLikeThis();
        $mltQuery->setLikeText('fake gmail sample');
        $mltQuery->setFields(array('email','content'));
        $mltQuery->setMaxQueryTerms(1);
        $mltQuery->setMinDocFrequency(1);
        $mltQuery->setMinTermFrequency(1);

        $query = new Query();
        $query->setFields(array('email', 'content'));
        $query->setQuery($mltQuery);

        $resultSet = $type->search($query);
        $resultSet->getResponse()->getData();
        $this->assertEquals(2, $resultSet->count());
    }

    public function testSetFields()
    {
        $query = new MoreLikeThis();

        $fields = array('firstname', 'lastname');
        $query->setFields($fields);

        $data = $query->toArray();
        $this->assertEquals($fields, $data['more_like_this']['fields']);
    }

    public function testSetLikeText()
    {
        $query = new MoreLikeThis();
        $query->setLikeText(' hello world');

        $data = $query->toArray();
        $this->assertEquals('hello world', $data['more_like_this']['like_text']);
    }

    public function testSetBoost()
    {
        $query = new MoreLikeThis();

        $boost = 1.3;
        $query->setBoost($boost);

        $this->assertEquals($boost, $query->getParam('boost'));
    }

    public function testSetMaxQueryTerms()
    {
        $query = new MoreLikeThis();

        $max = 3;
        $query->setMaxQueryTerms($max);

        $this->assertEquals($max, $query->getParam('max_query_terms'));
    }

    public function testSetPercentTermsToMatch()
    {
        $query = new MoreLikeThis();

        $match = 0.8;
        $query->setPercentTermsToMatch($match);

        $this->assertEquals($match, $query->getParam('percent_terms_to_match'));
    }

    public function testSetMinDocFrequency()
    {
        $query = new MoreLikeThis();

        $freq = 2;
        $query->setMinDocFrequency($freq);

        $this->assertEquals($freq, $query->getParam('min_doc_freq'));
    }

    public function testSetMaxDocFrequency()
    {
        $query = new MoreLikeThis();

        $freq = 2;
        $query->setMaxDocFrequency($freq);

        $this->assertEquals($freq, $query->getParam('max_doc_freq'));
    }

    public function testSetMinWordLength()
    {
        $query = new MoreLikeThis();

        $length = 4;
        $query->setMinWordLength($length);

        $this->assertEquals($length, $query->getParam('min_word_length'));
    }

    public function testSetMaxWordLength()
    {
        $query = new MoreLikeThis();

        $length = 5;
        $query->setMaxWordLength($length);

        $this->assertEquals($length, $query->getParam('max_word_length'));
    }

    public function testSetBoostTerms()
    {
        $query = new MoreLikeThis();

        $boost = false;
        $query->setBoostTerms($boost);

        $this->assertEquals($boost, $query->getParam('boost_terms'));
    }

    public function testSetAnalyzer()
    {
        $query = new MoreLikeThis();

        $analyzer = 'UpperCase';
        $query->setAnalyzer($analyzer);

        $this->assertEquals($analyzer, $query->getParam('analyzer'));
    }

    public function testSetStopWords()
    {
        $query = new MoreLikeThis();

        $stopWords = array('no', 'yes', 'test');
        $query->setStopWords($stopWords);

        $this->assertEquals($stopWords, $query->getParam('stop_words'));
    }
}
