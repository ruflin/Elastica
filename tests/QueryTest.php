<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastica\Aggregation\Terms as TermsAggregation;
use Elastica\Collapse;
use Elastica\Collapse\InnerHits;
use Elastica\Document;
use Elastica\Exception\InvalidException;
use Elastica\Mapping;
use Elastica\PointInTime;
use Elastica\Query;
use Elastica\Query\Term;
use Elastica\Query\Terms;
use Elastica\Rescore\Query as RescoreQuery;
use Elastica\Script\Script;
use Elastica\Script\ScriptFields;
use Elastica\Search;
use Elastica\Suggest;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class QueryTest extends BaseTest
{
    #[Group('unit')]
    public function testRawQuery(): void
    {
        $textQuery = new Term(['title' => 'test']);

        $query1 = Query::create($textQuery);

        $query2 = new Query();
        $query2->setRawQuery(['query' => ['term' => ['title' => 'test']]]);

        $this->assertEquals($query1->toArray(), $query2->toArray());
    }

    #[Group('unit')]
    public function testSuggestShouldNotRemoveOtherParameters(): void
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

    #[Group('unit')]
    public function testArrayQuery(): void
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

    #[Group('functional')]
    public function testSetSort(): void
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'firstname' => ['type' => 'text', 'fielddata' => true],
            'lastname' => ['type' => 'text', 'fielddata' => true],
        ]));

        $index->addDocuments([
            new Document('1', ['name' => 'hello world']),
            new Document('2', ['firstname' => 'guschti', 'lastname' => 'ruflin']),
            new Document('3', ['firstname' => 'nicolas', 'lastname' => 'ruflin']),
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

    #[Group('unit')]
    public function testAddSort(): void
    {
        $query = new Query();
        $sortParam = ['firstname' => ['order' => 'asc']];
        $query->addSort($sortParam);

        $this->assertEquals($query->getParam('sort'), [$sortParam]);
    }

    #[Group('unit')]
    public function testSetTrackScores(): void
    {
        $query = new Query();
        $param = false;
        $query->setTrackScores($param);

        $this->assertEquals($param, $query->getParam('track_scores'));
    }

    #[Group('unit')]
    public function testSetRawQuery(): void
    {
        $query = new Query();

        $params = ['query' => ['term' => ['title' => 'test']]];
        $query->setRawQuery($params);

        $this->assertEquals($params, $query->toArray());
    }

    #[Group('unit')]
    public function testSetStoredFields(): void
    {
        $query = (new Query())
            ->setStoredFields(['firstname', 'lastname'])
        ;

        $data = $query->toArray();

        $this->assertArrayHasKey('stored_fields', $data);
        $this->assertContains('firstname', $data['stored_fields']);
        $this->assertContains('lastname', $data['stored_fields']);
        $this->assertCount(2, $data['stored_fields']);
    }

    #[Group('unit')]
    public function testGetQuery(): void
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

    #[Group('unit')]
    public function testSetQueryToArrayCast(): void
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

    #[Group('unit')]
    public function testNotCloneInnerObjects(): void
    {
        $query = new Query();
        $termQuery = new Term();
        $termQuery->setTerm('text', 'value');
        $query->setQuery($termQuery);

        $anotherQuery = clone $query;

        $termQuery->setTerm('text', 'another value');

        $this->assertEquals($query->toArray(), $anotherQuery->toArray());
    }

    #[Group('unit')]
    public function testSetQueryToArrayChangeQuery(): void
    {
        $query = new Query();
        $termQuery = new Term();
        $termQuery->setTerm('text', 'value');
        $query->setQuery($termQuery);

        $queryArray = $query->toArray();

        $termQuery = $query->getQuery();
        $this->assertInstanceOf(Term::class, $termQuery);
        $termQuery->setTerm('text', 'another value');

        $this->assertNotEquals($queryArray, $query->toArray());
    }

    #[Group('unit')]
    public function testSetScriptFieldsToArrayCast(): void
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

    #[Group('unit')]
    public function testAddScriptFieldsToArrayCast(): void
    {
        $query = new Query();
        $scriptField = new Script('script');

        $query->addScriptField('script', $scriptField);

        $scriptField->setScript('another script');

        $anotherQuery = new Query();
        $anotherQuery->addScriptField('script', $scriptField);

        $this->assertEquals($query->toArray(), $anotherQuery->toArray());
    }

    #[Group('unit')]
    public function testAddScriptFieldToExistingScriptFields(): void
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

    #[Group('unit')]
    public function testAddAggregationToArrayCast(): void
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

    #[Group('unit')]
    public function testSetSuggestToArrayCast(): void
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

    #[Group('unit')]
    public function testSetRescoreToArrayCast(): void
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

    #[Group('unit')]
    public function testSetPostFilterToArrayCast(): void
    {
        $query = new Query();
        $postFilter = new Terms('key', ['term']);
        $query->setPostFilter($postFilter);

        $postFilter->addTerm('another term');

        $anotherQuery = new Query();
        $anotherQuery->setPostFilter($postFilter);

        $this->assertEquals($query->toArray(), $anotherQuery->toArray());
    }

    #[Group('unit')]
    public function testRawPostFilter(): void
    {
        $query = (new Query())
            ->setQuery(new Term(['field' => 'value']))
            ->setParam('post_filter', [
                'term' => ['field' => 'value'],
            ])
        ;

        $this->assertSame([
            'query' => [
                'term' => [
                    'field' => 'value',
                ],
            ],
            'post_filter' => [
                'term' => [
                    'field' => 'value',
                ],
            ],
        ], $query->toArray());
    }

    #[Group('functional')]
    public function testNoSource(): void
    {
        $index = $this->_createIndex();

        // Adds 1 document to the index
        $doc1 = new Document(
            '1',
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

    #[Group('unit')]
    public function testSetCollapseToArrayCast(): void
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

    #[Group('unit')]
    public function testCollapseArrayStructure(): void
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

    #[Group('unit')]
    public function testCollapseSecondLevelArrayStructure(): void
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

    #[Group('unit')]
    public function testIndicesBoostQuery(): void
    {
        $query = (new Query(new Term(['field' => 'value'])))
            ->setIndicesBoost(['foo' => 1.0, 'bar' => 2])
        ;

        $expected = [
            'query' => [
                'term' => [
                    'field' => 'value',
                ],
            ],
            'indices_boost' => [
                ['foo' => 1.0],
                ['bar' => 2],
            ],
        ];

        $this->assertSame($expected, $query->toArray());
    }

    #[Group('unit')]
    public function testPointInTimeInQuery(): void
    {
        $query = new Query();
        $query->setPointInTime(new PointInTime('id-hash', '5m'));
        $queryAsArray = $query->toArray();

        $this->assertArrayHasKey('pit', $queryAsArray);
        $this->assertArrayHasKey('id', $queryAsArray['pit']);
        $this->assertSame('id-hash', $queryAsArray['pit']['id']);
        $this->assertArrayHasKey('keep_alive', $queryAsArray['pit']);
        $this->assertSame('5m', $queryAsArray['pit']['keep_alive']);
    }

    #[Group('functional')]
    public function testPointInTimeQueryExecution(): void
    {
        $index = $this->_createIndex();
        $pitId = $index->openPointInTime('20s')->getData()['id'];

        $query = (new Query())->setPointInTime(new PointInTime($pitId, '20s'));
        $search = (new Search($index->getClient()))->setQuery($query);

        $this->assertSame($pitId, $search->search()->getPointInTimeId());
    }

    #[Group('unit')]
    public function testSetTrackTotalHitsIsInParams(): void
    {
        $query = new Query();
        $query->setTrackTotalHits(false);

        $this->assertFalse($query->getParam('track_total_hits'));
    }

    #[DataProvider('provideSetTrackTotalHitsInvalidValue')]
    #[Group('functional')]
    public function testSetTrackTotalHitsInvalidValue($value): void
    {
        $this->expectException(InvalidException::class);

        (new Query())->setTrackTotalHits($value);
    }

    public static function provideSetTrackTotalHitsInvalidValue(): iterable
    {
        yield 'string' => ['string string'];
        yield 'null' => [null];
        yield 'object' => [new \stdClass()];
        yield 'array' => [[]];
    }

    #[Group('functional')]
    public function testSetTrackTotalHits(): void
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'firstname' => ['type' => 'text', 'fielddata' => true],
        ]));

        $documents = [];
        for ($i = 0; $i < 50; ++$i) {
            $documents[] = new Document((string) $i, ['firstname' => 'antoine '.$i]);
        }

        $index->addDocuments($documents);

        $queryTerm = new Term();
        $queryTerm->setTerm('firstname', 'antoine');

        $index->refresh();

        $query = Query::create($queryTerm);

        $resultSet = $index->search($query);
        $this->assertEquals(50, $resultSet->getTotalHits());
        $this->assertEquals('eq', $resultSet->getTotalHitsRelation());

        $query->setTrackTotalHits(false);
        $resultSet = $index->search($query);
        $this->assertEquals(0, $resultSet->getTotalHits());
        $this->assertEquals('eq', $resultSet->getTotalHitsRelation());

        $query->setTrackTotalHits(25);
        $resultSet = $index->search($query);
        $this->assertEquals(25, $resultSet->getTotalHits());
        $this->assertEquals('gte', $resultSet->getTotalHitsRelation());
    }
}
