<?php

namespace Elastica\Test;

use Elastica\Aggregation\Terms as TermsAggregation;
use Elastica\Collapse;
use Elastica\Collapse\InnerHits;
use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Mapping;
use Elastica\Query;
use Elastica\Query\Term;
use Elastica\Query\Terms;
use Elastica\Rescore\Query as RescoreQuery;
use Elastica\Script\Script;
use Elastica\Script\ScriptFields;
use Elastica\Suggest;
use Elastica\Test\Base as BaseTest;

class QueryTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testRawQuery()
    {
        $textQuery = new Term(['title' => 'test']);

        $query1 = Query::create($textQuery);

        $query2 = new Query();
        $query2->setRawQuery(['query' => ['term' => ['title' => 'test']]]);

        $this->assertEquals($query1->toArray(), $query2->toArray());
    }

    /**
     * @group unit
     */
    public function testSuggestShouldNotRemoveOtherParameters()
    {
        $query1 = new Query();
        $query2 = new Query();

        $suggest = new Suggest();
        $suggest->setGlobalText('test');

        $query1->setSize(40);
        $query1->setSuggest($suggest);

        $query2->setSuggest($suggest);
        $query2->setSize(40);

        $this->assertEquals($query1->toArray(), $query2->toArray());
    }

    /**
     * @group unit
     */
    public function testSetSuggestMustReturnQueryInstance()
    {
        $query = new Query();
        $suggest = new Suggest();
        $this->assertInstanceOf(Query::class, $query->setSuggest($suggest));
    }

    /**
     * @group unit
     */
    public function testArrayQuery()
    {
        $query = [
            'query' => [
                'text' => [
                    'title' => 'test',
                ],
            ],
        ];

        $query1 = Query::create($query);

        $query2 = new Query();
        $query2->setRawQuery(['query' => ['text' => ['title' => 'test']]]);

        $this->assertEquals($query1->toArray(), $query2->toArray());
    }

    /**
     * @group functional
     */
    public function testSetSort()
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'firstname' => ['type' => 'text', 'fielddata' => true],
            'lastname' => ['type' => 'text', 'fielddata' => true],
        ]));

        $index->addDocuments([
            new Document(1, ['name' => 'hello world']),
            new Document(2, ['firstname' => 'guschti', 'lastname' => 'ruflin']),
            new Document(3, ['firstname' => 'nicolas', 'lastname' => 'ruflin']),
        ]);
        $index->refresh();

        $queryTerm = new Term();
        $queryTerm->setTerm('lastname', 'ruflin');
        $query = Query::create($queryTerm);

        // ASC order
        $query->setSort([['firstname' => ['order' => 'asc']]]);
        $resultSet = $index->search($query);
        $this->assertEquals(2, $resultSet->count());

        $first = $resultSet->current()->getData();
        $resultSet->next();
        $second = $resultSet->current()->getData();

        $this->assertEquals('guschti', $first['firstname']);
        $this->assertEquals('nicolas', $second['firstname']);

        // DESC order
        $query->setSort(['firstname' => ['order' => 'desc']]);
        $resultSet = $index->search($query);
        $this->assertEquals(2, $resultSet->count());

        $first = $resultSet->current()->getData();
        $resultSet->next();
        $second = $resultSet->current()->getData();

        $this->assertEquals('nicolas', $first['firstname']);
        $this->assertEquals('guschti', $second['firstname']);
    }

    /**
     * @group unit
     */
    public function testAddSort()
    {
        $query = new Query();
        $sortParam = ['firstname' => ['order' => 'asc']];
        $query->addSort($sortParam);

        $this->assertEquals($query->getParam('sort'), [$sortParam]);
    }

    /**
     * @group unit
     */
    public function testSetTrackScores()
    {
        $query = new Query();
        $param = false;
        $query->setTrackScores($param);

        $this->assertEquals($param, $query->getParam('track_scores'));
    }

    /**
     * @group unit
     */
    public function testSetRawQuery()
    {
        $query = new Query();

        $params = ['query' => 'test'];
        $query->setRawQuery($params);

        $this->assertEquals($params, $query->toArray());
    }

    /**
     * @group unit
     */
    public function testSetStoredFields()
    {
        $query = new Query();

        $params = ['query' => 'test'];

        $query->setStoredFields(['firstname', 'lastname']);

        $data = $query->toArray();

        $this->assertContains('firstname', $data['stored_fields']);
        $this->assertContains('lastname', $data['stored_fields']);
        $this->assertCount(2, $data['stored_fields']);
    }

    /**
     * @group unit
     */
    public function testGetQuery()
    {
        $query = new Query();

        try {
            $query->getQuery();
            $this->fail('should throw exception because query does not exist');
        } catch (InvalidException $e) {
            $this->assertTrue(true);
        }

        $termQuery = new Term();
        $termQuery->setTerm('text', 'value');
        $query->setQuery($termQuery);

        $this->assertSame($termQuery, $query->getQuery());
    }

    /**
     * @group unit
     */
    public function testSetQueryToArrayCast()
    {
        $query = new Query();
        $termQuery = new Term();
        $termQuery->setTerm('text', 'value');
        $query->setQuery($termQuery);

        $termQuery->setTerm('text', 'another value');

        $anotherQuery = new Query();
        $anotherQuery->setQuery($termQuery);

        $this->assertEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group unit
     */
    public function testNotCloneInnerObjects()
    {
        $query = new Query();
        $termQuery = new Term();
        $termQuery->setTerm('text', 'value');
        $query->setQuery($termQuery);

        $anotherQuery = clone $query;

        $termQuery->setTerm('text', 'another value');

        $this->assertEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group unit
     */
    public function testSetQueryToArrayChangeQuery()
    {
        $query = new Query();
        $termQuery = new Term();
        $termQuery->setTerm('text', 'value');
        $query->setQuery($termQuery);

        $queryArray = $query->toArray();

        $termQuery = $query->getQuery();
        $termQuery->setTerm('text', 'another value');

        $this->assertNotEquals($queryArray, $query->toArray());
    }

    /**
     * @group unit
     */
    public function testSetScriptFieldsToArrayCast()
    {
        $query = new Query();
        $scriptFields = new ScriptFields();
        $scriptFields->addScript('script', new Script('script'));

        $query->setScriptFields($scriptFields);

        $scriptFields->addScript('another script', new Script('another script'));

        $anotherQuery = new Query();
        $anotherQuery->setScriptFields($scriptFields);

        $this->assertEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group unit
     */
    public function testAddScriptFieldsToArrayCast()
    {
        $query = new Query();
        $scriptField = new Script('script');

        $query->addScriptField('script', $scriptField);

        $scriptField->setScript('another script');

        $anotherQuery = new Query();
        $anotherQuery->addScriptField('script', $scriptField);

        $this->assertEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group unit
     */
    public function testAddScriptFieldToExistingScriptFields()
    {
        $script1 = new Script('s1');
        $script2 = new Script('s2');

        // add script1, then add script2
        $query = new Query();
        $scriptFields1 = new ScriptFields();
        $scriptFields1->addScript('script1', $script1);
        $query->setScriptFields($scriptFields1);
        $query->addScriptField('script2', $script2);

        // add script1 and script2 at once
        $anotherQuery = new Query();
        $scriptFields2 = new ScriptFields();
        $scriptFields2->addScript('script1', $script1);
        $scriptFields2->addScript('script2', $script2);
        $anotherQuery->setScriptFields($scriptFields2);

        $this->assertEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group unit
     */
    public function testAddAggregationToArrayCast()
    {
        $query = new Query();
        $aggregation = new TermsAggregation('text');
        $aggregation->setField('field');

        $query->addAggregation($aggregation);

        $aggregation->setName('another text');

        $anotherQuery = new Query();
        $anotherQuery->addAggregation($aggregation);

        $this->assertEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group unit
     */
    public function testSetSuggestToArrayCast()
    {
        $query = new Query();
        $suggest = new Suggest();
        $suggest->setGlobalText('text');

        $query->setSuggest($suggest);

        $suggest->setGlobalText('another text');

        $anotherQuery = new Query();
        $anotherQuery->setSuggest($suggest);

        $this->assertEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group unit
     */
    public function testSetRescoreToArrayCast()
    {
        $query = new Query();
        $rescore = new RescoreQuery();
        $rescore->setQueryWeight(1);

        $query->setRescore($rescore);

        $rescore->setQueryWeight(2);

        $anotherQuery = new Query();
        $anotherQuery->setRescore($rescore);

        $this->assertEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group unit
     */
    public function testSetPostFilterToArrayCast()
    {
        $query = new Query();
        $postFilter = new Terms();
        $postFilter->setTerms('key', ['term']);
        $query->setPostFilter($postFilter);

        $postFilter->setTerms('another key', ['another term']);

        $anotherQuery = new Query();
        $anotherQuery->setPostFilter($postFilter);

        $this->assertEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group functional
     */
    public function testNoSource()
    {
        $index = $this->_createIndex();

        // Adds 1 document to the index
        $doc1 = new Document(1,
            ['username' => 'ruflin', 'test' => ['2', '3', '5']]
        );
        $index->addDocument($doc1);

        // To update index
        $index->refresh();

        $query = Query::create('ruflin');
        $resultSet = $index->search($query);

        // Disable source
        $query->setSource(false);

        $resultSetNoSource = $index->search($query);

        $this->assertEquals(1, $resultSet->count());
        $this->assertEquals(1, $resultSetNoSource->count());

        // Tests if no source is in response except id
        $result = $resultSetNoSource->current();
        $this->assertEquals(1, $result->getId());
        $this->assertEmpty($result->getData());

        // Tests if source is in response except id
        $result = $resultSet->current();
        $this->assertEquals(1, $result->getId());
        $this->assertNotEmpty($result->getData());
    }

    /**
     * @group unit
     */
    public function testSetCollapseToArrayCast()
    {
        $query = new Query();
        $collapse = new Collapse();
        $collapse->setFieldname('some_field');

        $query->setCollapse($collapse);

        $collapse->setFieldname('another_field');

        $anotherQuery = new Query();
        $anotherQuery->setCollapse($collapse);

        $this->assertEquals($query->toArray(), $anotherQuery->toArray());
    }

    /**
     * @group unit
     */
    public function testCollapseArrayStructure()
    {
        $query = new Query();
        $collapse = new Collapse();
        $collapse
            ->setFieldname('user')
            ->setInnerHits(
                (new InnerHits())
                    ->setName('last_tweets')
                    ->setSize(5)
                    ->setSort(['date' => 'asc'])
            )
            ->setMaxConcurrentGroupSearches(4)
        ;

        $query->setCollapse($collapse);

        $expected = [
            'field' => 'user',
            'inner_hits' => [
                'name' => 'last_tweets',
                'size' => 5,
                'sort' => ['date' => 'asc'],
            ],
            'max_concurrent_group_searches' => 4,
        ];

        $actual = $query->toArray();

        $this->assertArrayHasKey('collapse', $actual);
        $this->assertEquals($expected, $actual['collapse']);
    }

    /**
     * @group unit
     */
    public function testCollapseSecondLevelArrayStructure()
    {
        $query = new Query();
        $collapse = new Collapse();
        $collapse
            ->setFieldname('user')
            ->setInnerHits(
                (new InnerHits())
                    ->setName('last_tweets')
                    ->setSize(5)
                    ->setSort(['date' => 'asc'])
                    ->setCollapse(
                        (new Collapse())
                        ->setFieldname('date')
                    )
            )
            ->setMaxConcurrentGroupSearches(4)
        ;

        $query->setCollapse($collapse);

        $expected = [
            'field' => 'user',
            'inner_hits' => [
                'name' => 'last_tweets',
                'size' => 5,
                'sort' => ['date' => 'asc'],
                'collapse' => [
                    'field' => 'date',
                ],
            ],
            'max_concurrent_group_searches' => 4,
        ];

        $actual = $query->toArray();

        $this->assertArrayHasKey('collapse', $actual);
        $this->assertEquals($expected, $actual['collapse']);
    }

    /**
     * @group unit
     */
    public function testSetTrackTotalHitsIsInParams()
    {
        $query = new Query();
        $param = false;
        $query->setTrackTotalHits($param);

        $this->assertEquals($param, $query->getParam('track_total_hits'));
    }

    /**
     * @group functional
     */
    public function testSetTrackTotalHits()
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'firstname' => ['type' => 'text', 'fielddata' => true],
        ]));

        $documents = [];
        for ($i = 0; $i < 50; ++$i) {
            $documents[] = new Document($i, ['firstname' => 'antoine '.$i]);
        }

        $index->addDocuments($documents);

        $queryTerm = new Term();
        $queryTerm->setTerm('firstname', 'antoine');

        $index->refresh();

        $query = Query::create($queryTerm);

        $resultSet = $index->search($query);
        $this->assertEquals(50, $resultSet->getTotalHits());

        $query->setTrackTotalHits(false);
        $resultSet = $index->search($query);
        $this->assertEquals(0, $resultSet->getTotalHits());

        $query->setTrackTotalHits(25);
        $resultSet = $index->search($query);
        $this->assertEquals(25, $resultSet->getTotalHits());
    }
}
