<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Query\FuzzyLikeThis;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;
use Elastica\Type\Mapping;

class FuzzyLikeThisTest extends BaseTest
{
    public function testSearch()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test');
        $index->create(array(), true);
        $index->getSettings()->setNumberOfReplicas(0);
        //$index->getSettings()->setNumberOfShards(1);

        $type = new Type($index, 'helloworldfuzzy');
        $mapping = new Mapping($type, array(
               'email' => array('store' => 'yes', 'type' => 'string', 'index' => 'analyzed'),
               'content' => array('store' => 'yes', 'type' => 'string',  'index' => 'analyzed'),
          ));

        $mapping->setSource(array('enabled' => false));
        $type->setMapping($mapping);

        $doc = new Document(1000, array('email' => 'testemail@gmail.com', 'content' => 'This is a sample post. Hello World Fuzzy Like This!'));
        $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $fltQuery = new FuzzyLikeThis();
        $fltQuery->setLikeText("sample gmail");
        $fltQuery->addFields(array("email", "content"));
        $fltQuery->setMinSimilarity(0.3);
        $fltQuery->setMaxQueryTerms(3);
        $resultSet = $type->search($fltQuery);
        $this->assertEquals(1, $resultSet->count());
    }

    public function testSetPrefixLength()
    {
        $query = new FuzzyLikeThis();

        $length = 3;
        $query->setPrefixLength($length);

        $data = $query->toArray();

        $this->assertEquals($length, $data['fuzzy_like_this']['prefix_length']);
    }

    public function testAddFields()
    {
        $query = new FuzzyLikeThis();

        $fields = array('test1', 'test2');
        $query->addFields($fields);

        $data = $query->toArray();

        $this->assertEquals($fields, $data['fuzzy_like_this']['fields']);
    }

    public function testSetLikeText()
    {
        $query = new FuzzyLikeThis();

        $text = ' hello world';
        $query->setLikeText($text);

        $data = $query->toArray();

        $this->assertEquals(trim($text), $data['fuzzy_like_this']['like_text']);
    }

    public function testSetIgnoreTF()
    {
        $query = new FuzzyLikeThis();

        $ignoreTF = false;
        $query->setIgnoreTF($ignoreTF);
        $data = $query->toArray();
        $this->assertEquals($ignoreTF, $data['fuzzy_like_this']['ignore_tf']);

        $ignoreTF = true;
        $query->setIgnoreTF($ignoreTF);
        $data = $query->toArray();
        $this->assertEquals($ignoreTF, $data['fuzzy_like_this']['ignore_tf']);
    }

    public function testSetIgnoreTFDefault()
    {
        $query = new FuzzyLikeThis();

        $data = $query->toArray();

        $defaultIgnoreTF = false;
        $this->assertEquals($defaultIgnoreTF, $data['fuzzy_like_this']['ignore_tf']);
    }

    public function testSetMinSimilarity()
    {
        $query = new FuzzyLikeThis();

        $similarity = 2;
        $query->setMinSimilarity($similarity);

        $data = $query->toArray();

        $this->assertEquals($similarity, $data['fuzzy_like_this']['min_similarity']);
    }

    public function testSetBoost()
    {
        $query = new FuzzyLikeThis();

        $boost = 2.2;
        $query->setBoost($boost);

        $data = $query->toArray();

        $this->assertEquals($boost, $data['fuzzy_like_this']['boost']);
    }

    public function testAddAnalyzerViasetParam()
    {
        $analyzer = 'snowball';

        $query = new FuzzyLikeThis();
        $query->setParam('analyzer', $analyzer);

        $data = $query->toArray();
        $this->assertEquals($analyzer, $data['fuzzy_like_this']['analyzer']);
    }

    public function testSetAnalyzer()
    {
        $analyzer = 'snowball';

        $query = new FuzzyLikeThis();
        $query->setAnalyzer($analyzer);

        $data = $query->toArray();
        $this->assertEquals($analyzer, $data['fuzzy_like_this']['analyzer']);
    }

    public function testAnalyzerNotPresentInArrayToMaintainDefaultOfField()
    {
        $query = new FuzzyLikeThis();

        $data = $query->toArray();
        $this->assertArrayNotHasKey('analyzer', $data);
    }

    public function testArgArrayFieldsOverwrittenBySetParams()
    {
        $query = new FuzzyLikeThis();
        $query->setMaxQueryTerms(100);
        $query->setParam('max_query_terms', 200);

        $data = $query->toArray();
        $this->assertEquals(200, $data['fuzzy_like_this']['max_query_terms']);
    }

    public function testSearchSetAnalyzer()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test');
        $index->create(array('analysis' => array(
            'analyzer' => array(
               'searchAnalyzer' => array(
                    'type' => 'custom',
                    'tokenizer' => 'standard',
                    'filter' => array('myStopWords'),
                ),
            ),
            'filter' => array(
                'myStopWords' => array(
                    'type' => 'stop',
                    'stopwords' => array('The'),
                ),
            ),
        )), true);

        $index->getSettings()->setNumberOfReplicas(0);
        //$index->getSettings()->setNumberOfShards(1);

        $type = new Type($index, 'helloworldfuzzy');
        $mapping = new Mapping($type, array(
               'email' => array('store' => 'yes', 'type' => 'string', 'index' => 'analyzed'),
               'content' => array('store' => 'yes', 'type' => 'string',  'index' => 'analyzed'),
          ));

        $mapping->setSource(array('enabled' => false));
        $type->setMapping($mapping);

        $doc = new Document(1000, array('email' => 'testemail@gmail.com', 'content' => 'The Fuzzy Test!'));
        $type->addDocument($doc);

        $doc = new Document(1001, array('email' => 'testemail@gmail.com', 'content' => 'Elastica Fuzzy Test'));
        $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $fltQuery = new FuzzyLikeThis();
        $fltQuery->addFields(array("email", "content"));
        $fltQuery->setLikeText("The");

        $fltQuery->setMinSimilarity(0.1);
        $fltQuery->setMaxQueryTerms(3);

        // Test before analyzer applied, should return 1 result
        $resultSet = $type->search($fltQuery);
        $this->assertEquals(1, $resultSet->count());

        $fltQuery->setParam('analyzer', 'searchAnalyzer');

        $resultSet = $type->search($fltQuery);
        $this->assertEquals(0, $resultSet->count());
    }

    public function testNoLikeTextProvidedShouldReturnNoResults()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test');
        $index->create(array(), true);
        $index->getSettings()->setNumberOfReplicas(0);

        $type = new Type($index, 'helloworldfuzzy');
        $mapping = new Mapping($type, array(
                'email' => array('store' => 'yes', 'type' => 'string', 'index' => 'analyzed'),
                'content' => array('store' => 'yes', 'type' => 'string',  'index' => 'analyzed'),
            ));

        $mapping->setSource(array('enabled' => false));
        $type->setMapping($mapping);

        $doc = new Document(1000, array('email' => 'testemail@gmail.com', 'content' => 'This is a sample post. Hello World Fuzzy Like This!'));
        $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $fltQuery = new FuzzyLikeThis();
        $fltQuery->setLikeText("");
        $fltQuery->addFields(array("email", "content"));
        $fltQuery->setMinSimilarity(0.3);
        $fltQuery->setMaxQueryTerms(3);
        $resultSet = $type->search($fltQuery);

        $this->assertEquals(0, $resultSet->count());
    }
}
