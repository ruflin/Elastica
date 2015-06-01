<?php
namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Query\BoolQuery;
use Elastica\Query\Ids;
use Elastica\Query\Term;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;

class BoolQueryTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $query = new BoolQuery();

        $idsQuery1 = new Ids();
        $idsQuery1->setIds(1);

        $idsQuery2 = new Ids();
        $idsQuery2->setIds(2);

        $idsQuery3 = new Ids();
        $idsQuery3->setIds(3);

        $boost = 1.2;
        $minMatch = 2;

        $query->setBoost($boost);
        $query->setMinimumNumberShouldMatch($minMatch);
        $query->addMust($idsQuery1);
        $query->addMustNot($idsQuery2);
        $query->addShould($idsQuery3->toArray());

        $expectedArray = array(
            'bool' => array(
                'must' => array($idsQuery1->toArray()),
                'should' => array($idsQuery3->toArray()),
                'minimum_number_should_match' => $minMatch,
                'must_not' => array($idsQuery2->toArray()),
                'boost' => $boost,
            ),
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * Test to resolve the following issue.
     *
     * @link https://groups.google.com/forum/?fromgroups#!topic/elastica-php-client/zK_W_hClfvU
     *
     * @group unit
     */
    public function testToArrayStructure()
    {
        $boolQuery = new BoolQuery();

        $term1 = new Term();
        $term1->setParam('interests', 84);

        $term2 = new Term();
        $term2->setParam('interests', 92);

        $boolQuery->addShould($term1)->addShould($term2);

        $jsonString = '{"bool":{"should":[{"term":{"interests":84}},{"term":{"interests":92}}]}}';
        $this->assertEquals($jsonString, json_encode($boolQuery->toArray()));
    }

    /**
     * @group functional
     */
    public function testSearch()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test');
        $index->create(array(), true);

        $type = new Type($index, 'helloworld');

        $doc = new Document(1, array('id' => 1, 'email' => 'hans@test.com', 'username' => 'hans', 'test' => array('2', '3', '5')));
        $type->addDocument($doc);
        $doc = new Document(2, array('id' => 2, 'email' => 'emil@test.com', 'username' => 'emil', 'test' => array('1', '3', '6')));
        $type->addDocument($doc);
        $doc = new Document(3, array('id' => 3, 'email' => 'ruth@test.com', 'username' => 'ruth', 'test' => array('2', '3', '7')));
        $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $boolQuery = new BoolQuery();
        $termQuery1 = new Term(array('test' => '2'));
        $boolQuery->addMust($termQuery1);
        $resultSet = $type->search($boolQuery);

        $this->assertEquals(2, $resultSet->count());

        $termQuery2 = new Term(array('test' => '5'));
        $boolQuery->addMust($termQuery2);
        $resultSet = $type->search($boolQuery);

        $this->assertEquals(1, $resultSet->count());

        $termQuery3 = new Term(array('username' => 'hans'));
        $boolQuery->addMust($termQuery3);
        $resultSet = $type->search($boolQuery);

        $this->assertEquals(1, $resultSet->count());

        $termQuery4 = new Term(array('username' => 'emil'));
        $boolQuery->addMust($termQuery4);
        $resultSet = $type->search($boolQuery);

        $this->assertEquals(0, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testEmptyBoolQuery()
    {
        $index = $this->_createIndex();
        $type = new Type($index, 'test');

        $docNumber = 3;
        for ($i = 0; $i < $docNumber; $i++) {
            $doc = new Document($i, array('email' => 'test@test.com'));
            $type->addDocument($doc);
        }

        $index->refresh();

        $boolQuery = new BoolQuery();

        $resultSet = $type->search($boolQuery);

        $this->assertEquals($resultSet->count(), $docNumber);
    }

    /**
     * @group functional
     */
    public function testOldObject()
    {
        if (version_compare(phpversion(), 7, '>=')) {
            self::markTestSkipped('These objects are not supported in PHP 7');
        }

        $index = $this->_createIndex();
        $type = new Type($index, 'test');

        $docNumber = 3;
        for ($i = 0; $i < $docNumber; $i++) {
            $doc = new Document($i, array('email' => 'test@test.com'));
            $type->addDocument($doc);
        }

        $index->refresh();

        $boolQuery = new \Elastica\Query\Bool();

        $resultSet = $type->search($boolQuery);

        $this->assertEquals($resultSet->count(), $docNumber);
    }
}
