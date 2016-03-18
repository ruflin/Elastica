<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Filter\BoolFilter;
use Elastica\Filter\Term;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\MoreLikeThis;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;
use Elastica\Type\Mapping;

class MoreLikeThisTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testSearch()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test');
        $index->create(array(), true);
        $index->getSettings()->setNumberOfReplicas(0);
        //$index->getSettings()->setNumberOfShards(1);

        $type = new Type($index, 'helloworldmlt');
        $mapping = new Mapping($type, array(
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
        $mltQuery->setLike('fake gmail sample');
        $mltQuery->setFields(array('email', 'content'));
        $mltQuery->setMaxQueryTerms(3);
        $mltQuery->setMinDocFrequency(1);
        $mltQuery->setMinTermFrequency(1);

        $query = new Query();
        $query->setQuery($mltQuery);

        $resultSet = $type->search($query);
        $resultSet->getResponse()->getData();
        $this->assertEquals(2, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testSearchByDocument()
    {
        $client = $this->_getClient(array('persistent' => false));
        $index = $client->getIndex('elastica_test');
        $index->create(array('index' => array('number_of_shards' => 1, 'number_of_replicas' => 0)), true);

        $type = new Type($index, 'mlt_test');

        $type->addDocuments(array(
            new Document(1, array('visible' => true, 'name' => 'bruce wayne batman')),
            new Document(2, array('visible' => true, 'name' => 'bruce wayne')),
            new Document(3, array('visible' => false, 'name' => 'bruce wayne')),
            new Document(4, array('visible' => true, 'name' => 'batman')),
            new Document(5, array('visible' => false, 'name' => 'batman')),
            new Document(6, array('visible' => true, 'name' => 'superman')),
            new Document(7, array('visible' => true, 'name' => 'spiderman')),
        ));

        $index->refresh();

        $doc = $type->getDocument(1);

        // Return all similar from id
        $mltQuery = new MoreLikeThis();

        $mltQuery->setMinTermFrequency(1);
        $mltQuery->setMinDocFrequency(1);

        $mltQuery->setLike($doc);

        $query = new Query($mltQuery);

        $resultSet = $type->search($query);
        $this->assertEquals(4, $resultSet->count());

        $mltQuery = new MoreLikeThis();

        $mltQuery->setMinTermFrequency(1);
        $mltQuery->setMinDocFrequency(1);

        $mltQuery->setLike($doc);

        $query = new Query\BoolQuery();
        $query->addMust($mltQuery);
        $this->hideDeprecated();

        // Return just the visible similar from id
        $filter = new Query\BoolQuery();
        $filterTerm = new Query\Term();
        $filterTerm->setTerm('visible', true);
        $filter->addMust($filterTerm);
        $query->addFilter($filter);
        $this->showDeprecated();
        $resultSet = $type->search($query);
        $this->assertEquals(2, $resultSet->count());

        // Return all similar from source
        $mltQuery = new MoreLikeThis();

        $mltQuery->setMinTermFrequency(1);
        $mltQuery->setMinDocFrequency(1);
        $mltQuery->setMinimumShouldMatch(90);

        $mltQuery->setLike(
            $type->getDocument(1)->setId('')
        );

        $query = new Query($mltQuery);

        $resultSet = $type->search($query);
        $this->assertEquals(1, $resultSet->count());

        // Legacy test with filter
        $mltQuery = new MoreLikeThis();

        $mltQuery->setMinTermFrequency(1);
        $mltQuery->setMinDocFrequency(1);

        $mltQuery->setLike($doc);

        $query = new Query\BoolQuery();
        $query->addMust($mltQuery);
        $this->hideDeprecated();
        // Return just the visible similar
        $filter = new BoolFilter();
        $filterTerm = new Term();
        $filterTerm->setTerm('visible', true);
        $filter->addMust($filterTerm);
        $query->addFilter($filter);
        $this->showDeprecated();
        $resultSet = $type->search($query);
        $this->assertEquals(2, $resultSet->count());
    }

    /**
     * @group unit
     */
    public function testSetFields()
    {
        $query = new MoreLikeThis();

        $fields = array('firstname', 'lastname');
        $query->setFields($fields);

        $data = $query->toArray();
        $this->assertEquals($fields, $data['more_like_this']['fields']);
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\DeprecatedException
     */
    public function testSetIds()
    {
        $query = new MoreLikeThis();
        $ids = array(1, 2, 3);
        $query->setIds($ids);
    }

    /**
     * @group unit
     */
    public function testSetLike()
    {
        $query = new MoreLikeThis();
        $query->setLike(' hello world');

        $data = $query->toArray();
        $this->assertEquals(' hello world', $data['more_like_this']['like']);
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\DeprecatedException
     */
    public function testSetLikeText()
    {
        $query = new MoreLikeThis();
        $query->setLikeText(' hello world');
    }

    /**
     * @group unit
     */
    public function testSetBoost()
    {
        $query = new MoreLikeThis();

        $boost = 1.3;
        $query->setBoost($boost);

        $this->assertEquals($boost, $query->getParam('boost'));
    }

    /**
     * @group unit
     */
    public function testSetMaxQueryTerms()
    {
        $query = new MoreLikeThis();

        $max = 3;
        $query->setMaxQueryTerms($max);

        $this->assertEquals($max, $query->getParam('max_query_terms'));
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\DeprecatedException
     */
    public function testSetPercentTermsToMatch()
    {
        $query = new MoreLikeThis();

        $match = 0.8;
        $query->setPercentTermsToMatch($match);
    }

    /**
     * @group unit
     */
    public function testSetMinimumShouldMatch()
    {
        $query = new MoreLikeThis();

        $match = '80%';
        $query->setMinimumShouldMatch($match);

        $this->assertEquals($match, $query->getParam('minimum_should_match'));
    }

    /**
     * @group unit
     */
    public function testSetMinDocFrequency()
    {
        $query = new MoreLikeThis();

        $freq = 2;
        $query->setMinDocFrequency($freq);

        $this->assertEquals($freq, $query->getParam('min_doc_freq'));
    }

    /**
     * @group unit
     */
    public function testSetMaxDocFrequency()
    {
        $query = new MoreLikeThis();

        $freq = 2;
        $query->setMaxDocFrequency($freq);

        $this->assertEquals($freq, $query->getParam('max_doc_freq'));
    }

    /**
     * @group unit
     */
    public function testSetMinWordLength()
    {
        $query = new MoreLikeThis();

        $length = 4;
        $query->setMinWordLength($length);

        $this->assertEquals($length, $query->getParam('min_word_length'));
    }

    /**
     * @group unit
     */
    public function testSetMaxWordLength()
    {
        $query = new MoreLikeThis();

        $length = 5;
        $query->setMaxWordLength($length);

        $this->assertEquals($length, $query->getParam('max_word_length'));
    }

    /**
     * @group unit
     */
    public function testSetBoostTerms()
    {
        $query = new MoreLikeThis();

        $boost = false;
        $query->setBoostTerms($boost);

        $this->assertEquals($boost, $query->getParam('boost_terms'));
    }

    /**
     * @group unit
     */
    public function testSetAnalyzer()
    {
        $query = new MoreLikeThis();

        $analyzer = 'UpperCase';
        $query->setAnalyzer($analyzer);

        $this->assertEquals($analyzer, $query->getParam('analyzer'));
    }

    /**
     * @group unit
     */
    public function testSetStopWords()
    {
        $query = new MoreLikeThis();

        $stopWords = array('no', 'yes', 'test');
        $query->setStopWords($stopWords);

        $this->assertEquals($stopWords, $query->getParam('stop_words'));
    }

    /**
     * @group unit
     */
    public function testToArrayForId()
    {
        $query = new MoreLikeThis();
        $query->setLike(new Document(1, array(), 'type', 'index'));

        $data = $query->toArray();

        $this->assertEquals(
            array('more_like_this' => array(
                'like' => array(
                    '_id' => 1,
                    '_type' => 'type',
                    '_index' => 'index',
                ),
            ),
            ),
            $data
        );
    }

    /**
     * @group unit
     */
    public function testToArrayForSource()
    {
        $query = new MoreLikeThis();
        $query->setLike(new Document('', array('Foo' => 'Bar'), 'type', 'index'));

        $data = $query->toArray();

        $this->assertEquals(
            array('more_like_this' => array(
                'like' => array(
                    '_type' => 'type',
                    '_index' => 'index',
                    'doc' => array(
                        'Foo' => 'Bar',
                    ),
                ),
            ),
            ),
            $data
        );
    }
}
