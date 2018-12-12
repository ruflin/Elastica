<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MoreLikeThis;
use Elastica\Query\Term;
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
        $index->create([], true);
        $index->getSettings()->setNumberOfReplicas(0);
        //$index->getSettings()->setNumberOfShards(1);

        $type = new Type($index, '_doc');
        $mapping = new Mapping($type, [
            'email' => ['store' => true, 'type' => 'text', 'index' => true],
            'content' => ['store' => true, 'type' => 'text',  'index' => true],
        ]);

        $mapping->setSource(['enabled' => false]);
        $type->setMapping($mapping);

        $doc = new Document(1000, ['email' => 'testemail@gmail.com', 'content' => 'This is a sample post. Hello World Fuzzy Like This!']);
        $type->addDocument($doc);

        $doc = new Document(1001, ['email' => 'nospam@gmail.com', 'content' => 'This is a fake nospam email address for gmail']);
        $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $mltQuery = new MoreLikeThis();
        $mltQuery->setLike('fake gmail sample');
        $mltQuery->setFields(['email', 'content']);
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
        $client = $this->_getClient(['persistent' => false]);
        $index = $client->getIndex('elastica_test');
        $index->create(['settings' => ['index' => ['number_of_shards' => 1, 'number_of_replicas' => 0]]], true);

        $type = new Type($index, '_doc');

        $type->addDocuments([
            new Document(1, ['visible' => true, 'name' => 'bruce wayne batman']),
            new Document(2, ['visible' => true, 'name' => 'bruce wayne']),
            new Document(3, ['visible' => false, 'name' => 'bruce wayne']),
            new Document(4, ['visible' => true, 'name' => 'batman']),
            new Document(5, ['visible' => false, 'name' => 'batman']),
            new Document(6, ['visible' => true, 'name' => 'superman']),
            new Document(7, ['visible' => true, 'name' => 'spiderman']),
        ]);

        $index->refresh();

        $doc = $type->getDocument(1);

        // Return all similar from id
        $mltQuery = new MoreLikeThis();
        $mltQuery->setMinTermFrequency(1);
        $mltQuery->setMinDocFrequency(1);
        $mltQuery->setLike($doc);

        $this->assertEquals(4, $type->count($mltQuery));

        $mltQuery = new MoreLikeThis();
        $mltQuery->setMinTermFrequency(1);
        $mltQuery->setMinDocFrequency(1);
        $mltQuery->setLike($doc);

        $bool = new BoolQuery();
        $bool->addMust($mltQuery);
        // Return just the visible similar from id
        $filterTerm = new Term();
        $filterTerm->setTerm('visible', true);
        $bool->addFilter($filterTerm);

        $this->assertEquals(2, $type->count($bool));

        // Return all similar from source
        $mltQuery = new MoreLikeThis();
        $mltQuery->setMinTermFrequency(1);
        $mltQuery->setMinDocFrequency(1);
        $mltQuery->setMinimumShouldMatch('100%');
        $mltQuery->setLike(
            $type->getDocument(1)->setId('')
        );

        $this->assertEquals(1, $type->count($mltQuery));
    }

    /**
     * @group unit
     */
    public function testSetFields()
    {
        $query = new MoreLikeThis();

        $fields = ['firstname', 'lastname'];
        $query->setFields($fields);

        $data = $query->toArray();
        $this->assertEquals($fields, $data['more_like_this']['fields']);
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

        $stopWords = ['no', 'yes', 'test'];
        $query->setStopWords($stopWords);

        $this->assertEquals($stopWords, $query->getParam('stop_words'));
    }

    /**
     * @group unit
     */
    public function testToArrayForId()
    {
        $query = new MoreLikeThis();
        $query->setLike(new Document(1, [], '_doc', 'index'));

        $data = $query->toArray();

        $this->assertEquals(
            ['more_like_this' => [
                'like' => [
                    '_id' => 1,
                    '_type' => '_doc',
                    '_index' => 'index',
                ],
            ],
            ],
            $data
        );
    }

    /**
     * @group unit
     */
    public function testToArrayForSource()
    {
        $query = new MoreLikeThis();
        $query->setLike(new Document('', ['Foo' => 'Bar'], '_doc', 'index'));

        $data = $query->toArray();

        $this->assertEquals(
            ['more_like_this' => [
                'like' => [
                    '_type' => '_doc',
                    '_index' => 'index',
                    'doc' => [
                        'Foo' => 'Bar',
                    ],
                ],
            ],
            ],
            $data
        );
    }
}
