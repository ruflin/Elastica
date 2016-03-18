<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Filter\Exists;
use Elastica\Filter\Term as TermFilter;
use Elastica\Index;
use Elastica\Query\BoolQuery;
use Elastica\Query\Ids;
use Elastica\Query\Term;
use Elastica\Query\Term as TermQuery;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;

class BoolQueryTest extends BaseTest
{
    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testAddFilterInvalid()
    {
        $query = new BoolQuery();
        $query->addFilter($this);
    }

    /**
     * @group unit
     */
    public function testAddFilterWithLegacyFilterDeprecated()
    {
        $this->hideDeprecated();
        $existsFilter = new Exists('test');
        $this->showDeprecated();

        $query = new BoolQuery('test');

        $errorsCollector = $this->startCollectErrors();
        $query->addFilter($existsFilter);
        $this->finishCollectErrors();

        $errorsCollector->assertOnlyDeprecatedErrors(
            array(
                'Deprecated: Elastica\Query\BoolQuery::addFilter passing AbstractFilter is deprecated. Pass AbstractQuery instead.',
            )
        );
    }

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

        $filter1 = new Term();
        $filter1->setTerm('test', '1');

        $filter2 = new Term();
        $filter2->setTerm('username', 'ruth');

        $boost = 1.2;
        $minMatch = 2;

        $query->setBoost($boost);
        $query->setMinimumNumberShouldMatch($minMatch);
        $query->addMust($idsQuery1);
        $query->addMustNot($idsQuery2);
        $query->addShould($idsQuery3->toArray());
        $query->addFilter($filter1);
        $query->addFilter($filter2);

        $expectedArray = array(
            'bool' => array(
                'must' => array($idsQuery1->toArray()),
                'should' => array($idsQuery3->toArray()),
                'filter' => array($filter1->toArray(), $filter2->toArray()),
                'minimum_number_should_match' => $minMatch,
                'must_not' => array($idsQuery2->toArray()),
                'boost' => $boost,
            ),
        );

        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group unit
     */
    public function testToArrayWithLegacyFilter()
    {
        $query = new BoolQuery();

        $idsQuery1 = new Ids();
        $idsQuery1->setIds(1);

        $idsQuery2 = new Ids();
        $idsQuery2->setIds(2);

        $idsQuery3 = new Ids();
        $idsQuery3->setIds(3);

        $this->hideDeprecated();
        $filter1 = new TermFilter();
        $filter1->setTerm('test', '1');

        $filter2 = new TermFilter();
        $filter2->setTerm('username', 'ruth');
        $this->showDeprecated();

        $boost = 1.2;
        $minMatch = 2;

        $query->setBoost($boost);
        $query->setMinimumNumberShouldMatch($minMatch);
        $query->addMust($idsQuery1);
        $query->addMustNot($idsQuery2);
        $query->addShould($idsQuery3->toArray());

        $this->hideDeprecated();
        $query->addFilter($filter1);
        $query->addFilter($filter2);
        $this->showDeprecated();

        $expectedArray = array(
            'bool' => array(
                'must' => array($idsQuery1->toArray()),
                'should' => array($idsQuery3->toArray()),
                'filter' => array($filter1->toArray(), $filter2->toArray()),
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

        $term1 = new TermQuery();
        $term1->setParam('interests', 84);

        $term2 = new TermQuery();
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

        $doc = new Document(1, array('id' => 1, 'email' => 'hans@test.com', 'username' => 'hans', 'test' => array('2', '4', '5')));
        $type->addDocument($doc);
        $doc = new Document(2, array('id' => 2, 'email' => 'emil@test.com', 'username' => 'emil', 'test' => array('1', '3', '6')));
        $type->addDocument($doc);
        $doc = new Document(3, array('id' => 3, 'email' => 'ruth@test.com', 'username' => 'ruth', 'test' => array('2', '3', '7')));
        $type->addDocument($doc);
        $doc = new Document(4, array('id' => 4, 'email' => 'john@test.com', 'username' => 'john', 'test' => array('2', '4', '8')));
        $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $boolQuery = new BoolQuery();
        $termQuery1 = new TermQuery(array('test' => '2'));
        $boolQuery->addMust($termQuery1);
        $resultSet = $type->search($boolQuery);

        $this->assertEquals(3, $resultSet->count());

        $termFilter = new Term(array('test' => '4'));
        $boolQuery->addFilter($termFilter);
        $resultSet = $type->search($boolQuery);

        $this->assertEquals(2, $resultSet->count());

        $termQuery2 = new TermQuery(array('test' => '5'));
        $boolQuery->addMust($termQuery2);
        $resultSet = $type->search($boolQuery);

        $this->assertEquals(1, $resultSet->count());

        $termQuery3 = new TermQuery(array('username' => 'hans'));
        $boolQuery->addMust($termQuery3);
        $resultSet = $type->search($boolQuery);

        $this->assertEquals(1, $resultSet->count());

        $termQuery4 = new TermQuery(array('username' => 'emil'));
        $boolQuery->addMust($termQuery4);
        $resultSet = $type->search($boolQuery);

        $this->assertEquals(0, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testSearchWithLegacyFilter()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test');
        $index->create(array(), true);

        $type = new Type($index, 'helloworld');

        $doc = new Document(1, array('id' => 1, 'email' => 'hans@test.com', 'username' => 'hans', 'test' => array('2', '4', '5')));
        $type->addDocument($doc);
        $doc = new Document(2, array('id' => 2, 'email' => 'emil@test.com', 'username' => 'emil', 'test' => array('1', '3', '6')));
        $type->addDocument($doc);
        $doc = new Document(3, array('id' => 3, 'email' => 'ruth@test.com', 'username' => 'ruth', 'test' => array('2', '3', '7')));
        $type->addDocument($doc);
        $doc = new Document(4, array('id' => 4, 'email' => 'john@test.com', 'username' => 'john', 'test' => array('2', '4', '8')));
        $type->addDocument($doc);

        // Refresh index
        $index->refresh();

        $boolQuery = new BoolQuery();
        $termQuery1 = new TermQuery(array('test' => '2'));
        $boolQuery->addMust($termQuery1);
        $resultSet = $type->search($boolQuery);

        $this->assertEquals(3, $resultSet->count());

        $this->hideDeprecated();
        $termFilter = new TermFilter(array('test' => '4'));
        $boolQuery->addFilter($termFilter);
        $this->showDeprecated();
        $resultSet = $type->search($boolQuery);

        $this->assertEquals(2, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testEmptyBoolQuery()
    {
        $index = $this->_createIndex();
        $type = new Type($index, 'test');

        $docNumber = 3;
        for ($i = 0; $i < $docNumber; ++$i) {
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
        for ($i = 0; $i < $docNumber; ++$i) {
            $doc = new Document($i, array('email' => 'test@test.com'));
            $type->addDocument($doc);
        }

        $index->refresh();

        $this->hideDeprecated();
        $boolQuery = new \Elastica\Query\Bool();
        $this->showDeprecated();

        $resultSet = $type->search($boolQuery);

        $this->assertEquals($resultSet->count(), $docNumber);
    }

    /**
     * @group unit
     */
    public function testOldObjectDeprecated()
    {
        if (version_compare(phpversion(), 7, '>=')) {
            self::markTestSkipped('These objects are not supported in PHP 7');
        }

        $this->hideDeprecated();
        $reflection = new \ReflectionClass(new \Elastica\Query\Bool());
        $this->showDeprecated();

        $this->assertFileDeprecated($reflection->getFileName(), 'Elastica\Query\Bool is deprecated. Use BoolQuery instead. From PHP7 bool is reserved word and this class will be removed in further Elastica releases');
    }
}
