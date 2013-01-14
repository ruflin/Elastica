<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Query\FuzzyLikeThisQuery;
use Elastica\Type;
use Elastica\Type\MappingType;
use Elastica\Test\Base as BaseTest;

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
        $mapping = new MappingType($type , array(
               'email' => array('store' => 'yes', 'type' => 'string', 'index' => 'analyzed'),
               'content' => array('store' => 'yes', 'type' => 'string',  'index' => 'analyzed'),
          ));

        $mapping->setSource(array('enabled' => false));
        $type->setMapping($mapping);

        $doc = new Document(1000, array('email' => 'testemail@gmail.com', 'content' => 'This is a sample post. Hello World Fuzzy Like This!'));
        $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $fltQuery = new FuzzyLikeThisQuery();
        $fltQuery->setLikeText("sample gmail");
        $fltQuery->addFields(array("email","content"));
        $fltQuery->setMinSimilarity(0.3);
        $fltQuery->setMaxQueryTerms(3);
        $resultSet = $type->search($fltQuery);
        $this->assertEquals(1, $resultSet->count());
    }

    public function testSetPrefixLength()
    {
        $query = new FuzzyLikeThisQuery();

        $length = 3;
        $query->setPrefixLength($length);

        $data = $query->toArray();

        $this->assertEquals($length, $data['fuzzy_like_this']['prefix_length']);
    }

    public function testAddFields()
    {
        $query = new FuzzyLikeThisQuery();

        $fields = array('test1', 'test2');
        $query->addFields($fields);

        $data = $query->toArray();

        $this->assertEquals($fields, $data['fuzzy_like_this']['fields']);
    }

    public function testSetLikeText()
    {
        $query = new FuzzyLikeThisQuery();

        $text = ' hello world';
        $query->setLikeText($text);

        $data = $query->toArray();

        $this->assertEquals(trim($text), $data['fuzzy_like_this']['like_text']);
    }

    public function testSetIgnoreTF()
    {
        $query = new FuzzyLikeThisQuery();

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
        $query = new FuzzyLikeThisQuery();

        $data = $query->toArray();

        $defaultIgnoreTF = false;
        $this->assertEquals($defaultIgnoreTF, $data['fuzzy_like_this']['ignore_tf']);
    }

    public function testSetMinSimilarity()
    {
        $query = new FuzzyLikeThisQuery();

        $similarity = 2;
        $query->setMinSimilarity($similarity);

        $data = $query->toArray();

        $this->assertEquals($similarity, $data['fuzzy_like_this']['min_similarity']);
    }

    public function testSetBoost()
    {
        $query = new FuzzyLikeThisQuery();

        $boost = 2.2;
        $query->setBoost($boost);

        $data = $query->toArray();

        $this->assertEquals($boost, $data['fuzzy_like_this']['boost']);
    }
}
