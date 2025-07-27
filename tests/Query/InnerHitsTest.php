<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query\AbstractQuery;
use Elastica\Query\HasChild;
use Elastica\Query\InnerHits;
use Elastica\Query\MatchAll;
use Elastica\Query\Nested;
use Elastica\Query\SimpleQueryString;
use Elastica\ResultSet;
use Elastica\Script\Script;
use Elastica\Script\ScriptFields;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class InnerHitsTest extends BaseTest
{
    #[Group('unit')]
    public function testSetSize(): void
    {
        $innerHits = (new InnerHits())
            ->setSize(12)
        ;

        $this->assertSame(12, $innerHits->getParam('size'));
    }

    #[Group('unit')]
    public function testSetFrom(): void
    {
        $innerHits = (new InnerHits())
            ->setFrom(12)
        ;

        $this->assertSame(12, $innerHits->getParam('from'));
    }

    #[Group('unit')]
    public function testSetSort(): void
    {
        $sort = ['last_activity_date' => ['order' => 'desc']];
        $innerHits = (new InnerHits())
            ->setSort($sort)
        ;

        $this->assertSame($sort, $innerHits->getParam('sort'));
    }

    #[Group('unit')]
    public function testSetSource(): void
    {
        $fields = ['title', 'tags'];
        $innerHits = (new InnerHits())
            ->setSource($fields)
        ;

        $this->assertSame($fields, $innerHits->getParam('_source'));
    }

    #[Group('unit')]
    public function testSetVersion(): void
    {
        $innerHits = (new InnerHits())
            ->setVersion(true)
        ;

        $this->assertTrue($innerHits->getParam('version'));

        $innerHits->setVersion(false);

        $this->assertFalse($innerHits->getParam('version'));
    }

    #[Group('unit')]
    public function testSetExplain(): void
    {
        $innerHits = (new InnerHits())
            ->setExplain(true)
        ;

        $this->assertTrue($innerHits->getParam('explain'));

        $innerHits->setExplain(false);

        $this->assertFalse($innerHits->getParam('explain'));
    }

    #[Group('unit')]
    public function testSetHighlight(): void
    {
        $highlight = [
            'fields' => [
                'title',
            ],
        ];
        $innerHits = (new InnerHits())
            ->setHighlight($highlight)
        ;

        $this->assertSame($highlight, $innerHits->getParam('highlight'));
    }

    #[Group('unit')]
    public function testSetFieldDataFields(): void
    {
        $fields = ['title', 'tags'];
        $innerHits = (new InnerHits())
            ->setFieldDataFields($fields)
        ;

        $this->assertSame($fields, $innerHits->getParam('docvalue_fields'));
    }

    #[Group('unit')]
    public function testSetScriptFields(): void
    {
        $script = new Script('1 + 2');
        $scriptFields = new ScriptFields(['three' => $script]);

        $innerHits = (new InnerHits())
            ->setScriptFields($scriptFields)
        ;

        $this->assertSame($scriptFields, $innerHits->getParam('script_fields'));
    }

    #[Group('unit')]
    public function testAddScriptField(): void
    {
        $script = new Script('2+3');
        $innerHits = (new InnerHits())
            ->addScriptField('five', $script)
        ;

        $this->assertSame(['five' => $script->toArray()], $innerHits->getParam('script_fields')->toArray());
    }

    #[Group('functional')]
    public function testInnerHitsNested(): void
    {
        $queryString = new SimpleQueryString('windows newton', ['title', 'users.name']);
        $innerHits = new InnerHits();

        $results = $this->getNestedQuery($queryString, $innerHits);
        $firstResult = \current($results->getResults());

        $innerHitsResults = $firstResult->getInnerHits();

        $this->assertEquals(4, $firstResult->getId());
        $this->assertEquals('Newton', $innerHitsResults['users']['hits']['hits'][0]['_source']['name']);
    }

    #[Group('functional')]
    public function testInnerHitsParentChildren(): void
    {
        $queryString = new SimpleQueryString('linux cool');
        $innerHits = new InnerHits();

        $results = $this->getParentChildQuery($queryString, $innerHits);
        $firstResult = \current($results->getResults());

        $innerHits = $firstResult->getInnerHits();

        $responses = $innerHits['answers']['hits']['hits'];
        $responsesId = [];

        foreach ($responses as $response) {
            $responsesId[] = $response['_id'];
        }

        $this->assertEquals(1, $firstResult->getId());
        $this->assertEquals([6, 7], $responsesId);
    }

    #[Group('functional')]
    public function testInnerHitsLimitedSource(): void
    {
        $innerHits = (new InnerHits())
            ->setSource(['users.name'])
        ;

        $results = $this->getNestedQuery(new MatchAll(), $innerHits);

        foreach ($results as $row) {
            $innerHitsResult = $row->getInnerHits();
            foreach ($innerHitsResult['users']['hits']['hits'] as $doc) {
                $this->assertArrayHasKey('name', $doc['_source']);
                $this->assertArrayNotHasKey('last_activity_date', $doc['_source']);
            }
        }
    }

    #[Group('functional')]
    public function testInnerHitsWithOffset(): void
    {
        $queryString = new SimpleQueryString('linux cool');
        $innerHits = (new InnerHits())
            ->setFrom(1)
        ;

        $results = $this->getParentChildQuery($queryString, $innerHits);
        $firstResult = \current($results->getResults());

        $innerHits = $firstResult->getInnerHits();

        $responses = $innerHits['answers']['hits']['hits'];

        $this->assertCount(1, $responses);
        $this->assertEquals(7, $responses[0]['_id']);
    }

    #[Group('functional')]
    public function testInnerHitsWithSort(): void
    {
        $queryString = new SimpleQueryString('linux cool');
        $innerHits = (new InnerHits())
            ->setSort(['answer' => 'asc'])
        ;

        $results = $this->getParentChildQuery($queryString, $innerHits);
        $firstResult = \current($results->getResults());

        $innerHits = $firstResult->getInnerHits();

        $responses = $innerHits['answers']['hits']['hits'];
        $responsesId = [];

        foreach ($responses as $response) {
            $responsesId[] = $response['_id'];
        }

        $this->assertEquals(1, $firstResult->getId());
        $this->assertEquals([7, 6], $responsesId);
    }

    #[Group('functional')]
    public function testInnerHitsWithExplain(): void
    {
        $matchAll = new MatchAll();
        $innerHits = (new InnerHits())
            ->setExplain(true)
        ;

        $results = $this->getNestedQuery($matchAll, $innerHits);

        foreach ($results as $row) {
            $innerHitsResult = $row->getInnerHits();
            foreach ($innerHitsResult['users']['hits']['hits'] as $doc) {
                $this->assertArrayHasKey('_explanation', $doc);
            }
        }
    }

    #[Group('functional')]
    public function testInnerHitsWithVersion(): void
    {
        $matchAll = new MatchAll();
        $innerHits = (new InnerHits())
            ->setVersion(true)
        ;

        $results = $this->getNestedQuery($matchAll, $innerHits);

        foreach ($results as $row) {
            $innerHitsResult = $row->getInnerHits();
            foreach ($innerHitsResult['users']['hits']['hits'] as $doc) {
                $this->assertArrayHasKey('_version', $doc);
            }
        }
    }

    #[Group('functional')]
    public function testInnerHitsWithScriptFields(): void
    {
        $matchAll = new MatchAll();
        $scriptFields = (new ScriptFields())
            ->addScript('three', new Script('1 + 2'))
            ->addScript('five', new Script('3 + 2'))
        ;
        $innerHits = (new InnerHits())
            ->setSize(1)
            ->setScriptFields($scriptFields)
        ;

        $results = $this->getNestedQuery($matchAll, $innerHits);

        foreach ($results as $row) {
            $innerHitsResult = $row->getInnerHits();
            foreach ($innerHitsResult['users']['hits']['hits'] as $doc) {
                $this->assertEquals(3, $doc['fields']['three'][0]);
                $this->assertEquals(5, $doc['fields']['five'][0]);
            }
        }
    }

    #[Group('functional')]
    public function testInnerHitsWithHighlight(): void
    {
        $queryString = new SimpleQueryString('question simon', ['title', 'users.name']);
        $innerHits = (new InnerHits())
            ->setHighlight(['fields' => ['users.name' => new \stdClass()]])
        ;

        $results = $this->getNestedQuery($queryString, $innerHits);

        foreach ($results as $row) {
            $innerHitsResult = $row->getInnerHits();
            foreach ($innerHitsResult['users']['hits']['hits'] as $doc) {
                $this->assertArrayHasKey('highlight', $doc);
                $this->assertMatchesRegularExpression('#<em>Simon</em>#', $doc['highlight']['users.name'][0]);
            }
        }
    }

    #[Group('functional')]
    public function testInnerHitsWithFieldData(): void
    {
        $queryString = new SimpleQueryString('question simon', ['title', 'users.name']);
        $innerHits = (new InnerHits())
            ->setFieldDataFields(['users.name'])
        ;

        $results = $this->getNestedQuery($queryString, $innerHits);

        foreach ($results as $row) {
            $innerHitsResult = $row->getInnerHits();
            foreach ($innerHitsResult['users']['hits']['hits'] as $doc) {
                $this->assertArrayHasKey('fields', $doc);
                $this->assertArrayHasKey('users.name', $doc['fields']);
                $this->assertArrayNotHasKey('users.last_activity_date', $doc['fields']);
            }
        }
    }

    private function _getIndexForNestedTest(): Index
    {
        $index = $this->_createIndex();
        $index->setMapping(new Mapping([
            'users' => [
                'type' => 'nested',
                'properties' => [
                    'name' => ['type' => 'text', 'fielddata' => true],
                    'last_activity_date' => ['type' => 'date'],
                ],
            ],
            'title' => ['type' => 'text'],
            'last_activity_date' => ['type' => 'date'],
        ]));

        $index->addDocuments([
            $index->createDocument('1', [
                'users' => [
                    ['name' => 'John Smith', 'last_activity_date' => '2015-01-05'],
                    ['name' => 'Conan', 'last_activity_date' => '2015-01-05'],
                ],
                'last_activity_date' => '2015-01-05',
                'title' => 'Question about linux #1',
            ]),
            $index->createDocument('2', [
                'users' => [
                    ['name' => 'John Doe', 'last_activity_date' => '2015-01-05'],
                    ['name' => 'Simon', 'last_activity_date' => '2015-01-05'],
                ],
                'last_activity_date' => '2014-12-23',
                'title' => 'Question about linux #2',
            ]),
            $index->createDocument('3', [
                'users' => [
                    ['name' => 'Simon', 'last_activity_date' => '2015-01-05'],
                    ['name' => 'Garfunkel', 'last_activity_date' => '2015-01-05'],
                ],
                'last_activity_date' => '2015-01-05',
                'title' => 'Question about windows #1',
            ]),
            $index->createDocument('4', [
                'users' => [
                    ['name' => 'Einstein'],
                    ['name' => 'Newton'],
                    ['name' => 'Maxwell'],
                ],
                'last_activity_date' => '2014-12-23',
                'title' => 'Question about windows #2',
            ]),
            $index->createDocument('5', [
                'users' => [
                    ['name' => 'Faraday'],
                    ['name' => 'Leibniz'],
                    ['name' => 'Descartes'],
                ],
                'last_activity_date' => '2014-12-23',
                'title' => 'Question about osx',
            ]),
        ]);

        $index->refresh();

        return $index;
    }

    private function _getIndexForParentChildrenTest(): Index
    {
        $index = $this->_createIndex();
        $mappingQuestion = new Mapping();
        $mappingQuestion->setProperties([
            'title' => ['type' => 'text'],
            'answer' => ['type' => 'text', 'fielddata' => true],
            'last_activity_date' => ['type' => 'date'],
            'my_join_field' => [
                'type' => 'join',
                'relations' => [
                    'questions' => 'answers',
                ],
            ],
        ]);

        $index->setMapping($mappingQuestion);
        $index->addDocuments([
            $index->createDocument('1', [
                'last_activity_date' => '2015-01-05',
                'title' => 'Question about linux #1',
                'my_join_field' => [
                    'name' => 'questions',
                ],
            ]),
            $index->createDocument('2', [
                'last_activity_date' => '2014-12-23',
                'title' => 'Question about linux #2',
                'my_join_field' => [
                    'name' => 'questions',
                ],
            ]),
            $index->createDocument('3', [
                'last_activity_date' => '2015-01-05',
                'title' => 'Question about windows #1',
                'my_join_field' => [
                    'name' => 'questions',
                ],
            ]),
            $index->createDocument('4', [
                'last_activity_date' => '2014-12-23',
                'title' => 'Question about windows #2',
                'my_join_field' => [
                    'name' => 'questions',
                ],
            ]),
            $index->createDocument('5', [
                'last_activity_date' => '2014-12-23',
                'title' => 'Question about osx',
                'my_join_field' => [
                    'name' => 'questions',
                ],
            ]),
        ]);

        $doc1 = $index->createDocument('6', [
            'answer' => 'linux is cool',
            'last_activity_date' => '2016-01-05',
            'my_join_field' => [
                'name' => 'answers',
                'parent' => 1,
            ],
        ]);

        $doc2 = $index->createDocument('7', [
            'answer' => 'linux is bad',
            'last_activity_date' => '2005-01-05',
            'my_join_field' => [
                'name' => 'answers',
                'parent' => 1,
            ],
        ]);

        $doc3 = $index->createDocument('8', [
            'answer' => 'windows was cool',
            'last_activity_date' => '2005-01-05',
            'my_join_field' => [
                'name' => 'answers',
                'parent' => 2,
            ],
        ]);

        $this->_getClient()->addDocuments([$doc1, $doc2, $doc3], ['routing' => 1]);

        $index->refresh();

        return $index;
    }

    private function getNestedQuery(AbstractQuery $query, InnerHits $innerHits): ResultSet
    {
        $nested = (new Nested())
            ->setInnerHits($innerHits)
            ->setPath('users')
            ->setQuery($query)
        ;

        return $this->_getIndexForNestedTest()->search($nested);
    }

    private function getParentChildQuery(AbstractQuery $query, InnerHits $innerHits): ResultSet
    {
        $child = (new HasChild($query, 'answers'))->setInnerHits($innerHits);

        return $this->_getIndexForParentChildrenTest()->search($child);
    }
}
