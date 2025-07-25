<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MoreLikeThis;
use Elastica\Query\Term;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class MoreLikeThisTest extends BaseTest
{
    #[Group('functional')]
    public function testSearch(): void
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test');
        $index->create([], [
            'recreate' => true,
        ]);
        $index->getSettings()->setNumberOfReplicas(0);

        $mapping = new Mapping([
            'email' => ['store' => true, 'type' => 'text', 'index' => true],
            'content' => ['store' => true, 'type' => 'text',  'index' => true],
        ]);

        $mapping->setSource(['enabled' => false]);
        $index->setMapping($mapping);

        $doc = new Document('1000', ['email' => 'testemail@gmail.com', 'content' => 'This is a sample post. Hello World Fuzzy Like This!']);
        $index->addDocument($doc);

        $doc = new Document('1001', ['email' => 'nospam@gmail.com', 'content' => 'This is a fake nospam email address for gmail']);
        $index->addDocument($doc);

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

        $resultSet = $index->search($query);
        $resultSet->getResponse()->getData();
        $this->assertEquals(2, $resultSet->count());
    }

    #[Group('functional')]
    public function testSearchByDocument(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('elastica_test');
        $index->create(
            [
                'settings' => [
                    'index' => [
                        'number_of_shards' => 1,
                        'number_of_replicas' => 0,
                    ],
                ],
            ],
            [
                'recreate' => true,
            ]
        );

        $index->addDocuments([
            new Document('1', ['visible' => true, 'name' => 'bruce wayne batman']),
            new Document('2', ['visible' => true, 'name' => 'bruce wayne']),
            new Document('3', ['visible' => false, 'name' => 'bruce wayne']),
            new Document('4', ['visible' => true, 'name' => 'batman']),
            new Document('5', ['visible' => false, 'name' => 'batman']),
            new Document('6', ['visible' => true, 'name' => 'superman']),
            new Document('7', ['visible' => true, 'name' => 'spiderman']),
        ]);

        $index->refresh();

        $doc = $index->getDocument(1);

        // Return all similar from id
        $mltQuery = new MoreLikeThis();
        $mltQuery->setMinTermFrequency(1);
        $mltQuery->setMinDocFrequency(1);
        $mltQuery->setLike($doc);

        $this->assertEquals(4, $index->count($mltQuery));

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

        $this->assertEquals(2, $index->count($bool));

        // Return all similar from source
        $mltQuery = new MoreLikeThis();
        $mltQuery->setMinTermFrequency(1);
        $mltQuery->setMinDocFrequency(1);
        $mltQuery->setMinimumShouldMatch('100%');
        $mltQuery->setLike(
            $index->getDocument(1)->setId('')
        );

        $this->assertEquals(1, $index->count($mltQuery));
    }

    #[Group('unit')]
    public function testSetFields(): void
    {
        $query = new MoreLikeThis();

        $fields = ['firstname', 'lastname'];
        $query->setFields($fields);

        $data = $query->toArray();
        $this->assertEquals($fields, $data['more_like_this']['fields']);
    }

    #[Group('unit')]
    public function testSetLike(): void
    {
        $query = new MoreLikeThis();
        $query->setLike(' hello world');

        $data = $query->toArray();
        $this->assertEquals(' hello world', $data['more_like_this']['like']);
    }

    #[Group('unit')]
    public function testSetBoost(): void
    {
        $query = new MoreLikeThis();

        $boost = 1.3;
        $query->setBoost($boost);

        $this->assertEquals($boost, $query->getParam('boost'));
    }

    #[Group('unit')]
    public function testSetMaxQueryTerms(): void
    {
        $query = new MoreLikeThis();

        $max = 3;
        $query->setMaxQueryTerms($max);

        $this->assertEquals($max, $query->getParam('max_query_terms'));
    }

    #[Group('unit')]
    public function testSetMinimumShouldMatch(): void
    {
        $query = new MoreLikeThis();

        $match = '80%';
        $query->setMinimumShouldMatch($match);

        $this->assertEquals($match, $query->getParam('minimum_should_match'));
    }

    #[Group('unit')]
    public function testSetMinDocFrequency(): void
    {
        $query = new MoreLikeThis();

        $freq = 2;
        $query->setMinDocFrequency($freq);

        $this->assertEquals($freq, $query->getParam('min_doc_freq'));
    }

    #[Group('unit')]
    public function testSetMaxDocFrequency(): void
    {
        $query = new MoreLikeThis();

        $freq = 2;
        $query->setMaxDocFrequency($freq);

        $this->assertEquals($freq, $query->getParam('max_doc_freq'));
    }

    #[Group('unit')]
    public function testSetMinWordLength(): void
    {
        $query = new MoreLikeThis();

        $length = 4;
        $query->setMinWordLength($length);

        $this->assertEquals($length, $query->getParam('min_word_length'));
    }

    #[Group('unit')]
    public function testSetMaxWordLength(): void
    {
        $query = new MoreLikeThis();

        $length = 5;
        $query->setMaxWordLength($length);

        $this->assertEquals($length, $query->getParam('max_word_length'));
    }

    #[Group('unit')]
    public function testSetBoostTerms(): void
    {
        $query = new MoreLikeThis();

        $boost = false;
        $query->setBoostTerms($boost);

        $this->assertEquals($boost, $query->getParam('boost_terms'));
    }

    #[Group('unit')]
    public function testSetAnalyzer(): void
    {
        $query = new MoreLikeThis();

        $analyzer = 'UpperCase';
        $query->setAnalyzer($analyzer);

        $this->assertEquals($analyzer, $query->getParam('analyzer'));
    }

    #[Group('unit')]
    public function testSetStopWords(): void
    {
        $query = new MoreLikeThis();

        $stopWords = ['no', 'yes', 'test'];
        $query->setStopWords($stopWords);

        $this->assertEquals($stopWords, $query->getParam('stop_words'));
    }

    #[Group('unit')]
    public function testToArrayForId(): void
    {
        $query = new MoreLikeThis();
        $query->setLike(new Document('1', [], 'index'));

        $data = $query->toArray();

        $this->assertEquals(
            ['more_like_this' => [
                'like' => [
                    '_id' => '1',
                    '_index' => 'index',
                ],
            ],
            ],
            $data
        );
    }

    #[Group('unit')]
    public function testToArrayForSource(): void
    {
        $query = new MoreLikeThis();
        $query->setLike(new Document('', ['Foo' => 'Bar'], 'index'));

        $data = $query->toArray();

        $this->assertEquals(
            ['more_like_this' => [
                'like' => [
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
