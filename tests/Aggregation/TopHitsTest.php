<?php

declare(strict_types=1);

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\AbstractAggregation;
use Elastica\Aggregation\Terms;
use Elastica\Aggregation\TopHits;
use Elastica\Document;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Query\SimpleQueryString;
use Elastica\Script\Script;
use Elastica\Script\ScriptFields;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class TopHitsTest extends BaseAggregationTestCase
{
    #[Group('unit')]
    public function testSetSize(): void
    {
        $agg = (new TopHits('agg_name'))
            ->setSize(12)
        ;

        $this->assertSame(12, $agg->getParam('size'));
    }

    #[Group('unit')]
    public function testSetFrom(): void
    {
        $agg = (new TopHits('agg_name'))
            ->setFrom(12)
        ;

        $this->assertEquals(12, $agg->getParam('from'));
    }

    #[Group('unit')]
    public function testSetSort(): void
    {
        $sort = ['last_activity_date' => ['order' => 'desc']];
        $agg = (new TopHits('agg_name'))
            ->setSort($sort)
        ;

        $this->assertSame($sort, $agg->getParam('sort'));
    }

    #[Group('unit')]
    public function testSetSource(): void
    {
        $fields = ['title', 'tags'];
        $agg = (new TopHits('agg_name'))
            ->setSource($fields)
        ;

        $this->assertSame($fields, $agg->getParam('_source'));
    }

    #[Group('unit')]
    public function testSetVersion(): void
    {
        $agg = (new TopHits('agg_name'))
            ->setVersion(true)
        ;

        $this->assertTrue($agg->getParam('version'));

        $agg->setVersion(false);
        $this->assertFalse($agg->getParam('version'));
    }

    #[Group('unit')]
    public function testSetExplain(): void
    {
        $agg = (new TopHits('agg_name'))
            ->setExplain(true)
        ;

        $this->assertTrue($agg->getParam('explain'));

        $agg->setExplain(false);
        $this->assertFalse($agg->getParam('explain'));
    }

    #[Group('unit')]
    public function testSetHighlight(): void
    {
        $highlight = [
            'fields' => [
                'title',
            ],
        ];
        $agg = (new TopHits('agg_name'))
            ->setHighlight($highlight)
        ;

        $this->assertSame($highlight, $agg->getParam('highlight'));
    }

    #[Group('unit')]
    public function testSetFieldDataFields(): void
    {
        $fields = ['title', 'tags'];
        $agg = (new TopHits('agg_name'))
            ->setFieldDataFields($fields)
        ;

        $this->assertSame($fields, $agg->getParam('docvalue_fields'));
    }

    #[Group('unit')]
    public function testSetScriptFields(): void
    {
        $script = new Script('1 + 2');
        $scriptFields = new ScriptFields(['three' => $script]);

        $agg = (new TopHits('agg_name'))
            ->setScriptFields($scriptFields)
        ;

        $this->assertSame($scriptFields, $agg->getParam('script_fields'));
    }

    #[Group('unit')]
    public function testAddScriptField(): void
    {
        $script = new Script('2+3');
        $agg = (new TopHits('agg_name'))
            ->addScriptField('five', $script)
        ;

        $this->assertEquals(['five' => $script->toArray()], $agg->getParam('script_fields')->toArray());
    }

    #[Group('functional')]
    public function testAggregateUpdatedRecently(): void
    {
        $agg = (new TopHits('top_tag_hits'))
            ->setSize(1)
            ->setSort(['last_activity_date' => ['order' => 'desc']])
        ;

        $resultDocs = [];
        $outerAggResult = $this->getOuterAggregationResult($agg);
        foreach ($outerAggResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $resultDocs[] = $doc['_id'];
            }
        }

        $this->assertEquals([1, 3], $resultDocs);
    }

    #[Group('functional')]
    public function testAggregateUpdatedFarAgo(): void
    {
        $agg = (new TopHits('top_tag_hits'))
            ->setSize(1)
            ->setSort(['last_activity_date' => ['order' => 'asc']])
        ;

        $resultDocs = [];
        $outerAggResult = $this->getOuterAggregationResult($agg);
        foreach ($outerAggResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $resultDocs[] = $doc['_id'];
            }
        }

        $this->assertEquals([2, 4], $resultDocs);
    }

    #[Group('functional')]
    public function testAggregateTwoDocumentPerTag(): void
    {
        $agg = (new TopHits('top_tag_hits'))
            ->setSize(2)
        ;

        $resultDocs = [];
        $outerAggResult = $this->getOuterAggregationResult($agg);
        foreach ($outerAggResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $resultDocs[] = $doc['_id'];
            }
        }

        $this->assertEquals([1, 2, 3, 4], $resultDocs);
    }

    #[Group('functional')]
    public function testAggregateTwoDocumentPerTagWithOffset(): void
    {
        $agg = (new TopHits('top_tag_hits'))
            ->setSize(2)
            ->setFrom(1)
            ->setSort(['last_activity_date' => ['order' => 'desc']])
        ;

        $resultDocs = [];
        $outerAggResult = $this->getOuterAggregationResult($agg);
        foreach ($outerAggResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $resultDocs[] = $doc['_id'];
            }
        }

        $this->assertEquals([2, 4], $resultDocs);
    }

    public static function limitedSourceProvider(): array
    {
        return [
            'string source' => ['title'],
            'array source' => [['title']],
        ];
    }

    #[DataProvider('limitedSourceProvider')]
    #[Group('functional')]
    public function testAggregateWithLimitedSource($source): void
    {
        $agg = (new TopHits('top_tag_hits'))
            ->setSource($source)
        ;

        $outerAggResult = $this->getOuterAggregationResult($agg);
        foreach ($outerAggResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $this->assertArrayHasKey('title', $doc['_source']);
                $this->assertArrayNotHasKey('tags', $doc['_source']);
                $this->assertArrayNotHasKey('last_activity_date', $doc['_source']);
            }
        }
    }

    #[Group('functional')]
    public function testAggregateWithVersion(): void
    {
        $agg = (new TopHits('top_tag_hits'))
            ->setVersion(true)
        ;

        $outerAggResult = $this->getOuterAggregationResult($agg);
        foreach ($outerAggResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $this->assertArrayHasKey('_version', $doc);
            }
        }
    }

    #[Group('functional')]
    public function testAggregateWithExplain(): void
    {
        $agg = (new TopHits('top_tag_hits'))
            ->setExplain(true)
        ;

        $outerAggResult = $this->getOuterAggregationResult($agg);
        foreach ($outerAggResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $this->assertArrayHasKey('_explanation', $doc);
            }
        }
    }

    #[Group('functional')]
    public function testAggregateWithScriptFields(): void
    {
        $agg = (new TopHits('top_tag_hits'))
            ->setSize(1)
            ->setScriptFields(['three' => new Script('1 + 2')])
            ->addScriptField('five', new Script('3 + 2'))
        ;

        $outerAggResult = $this->getOuterAggregationResult($agg);
        foreach ($outerAggResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $this->assertEquals(3, $doc['fields']['three'][0]);
                $this->assertEquals(5, $doc['fields']['five'][0]);
            }
        }
    }

    #[Group('functional')]
    public function testAggregateWithHighlight(): void
    {
        $queryString = new SimpleQueryString('linux', ['title']);

        $agg = (new TopHits('top_tag_hits'))
            ->setHighlight(['fields' => ['title' => new \stdClass()]])
        ;

        $query = new Query($queryString);
        $query->addAggregation($agg);

        $resultSet = $this->_getIndexForTest()->search($query);
        $aggResult = $resultSet->getAggregation('top_tag_hits');

        foreach ($aggResult['hits']['hits'] as $doc) {
            $this->assertArrayHasKey('highlight', $doc);
            $this->assertMatchesRegularExpression('#<em>linux</em>#', $doc['highlight']['title'][0]);
        }
    }

    #[Group('functional')]
    public function testAggregateWithFieldData(): void
    {
        $agg = (new TopHits('top_tag_hits'))
            ->setFieldDataFields(['title'])
        ;

        $query = new Query(new MatchAll());
        $query->addAggregation($agg);

        $resultSet = $this->_getIndexForTest()->search($query);
        $aggResult = $resultSet->getAggregation('top_tag_hits');

        foreach ($aggResult['hits']['hits'] as $doc) {
            $this->assertArrayHasKey('fields', $doc);
            $this->assertArrayHasKey('title', $doc['fields']);
            $this->assertArrayNotHasKey('tags', $doc['fields']);
            $this->assertArrayNotHasKey('last_activity_date', $doc['fields']);
        }
    }

    protected function _getIndexForTest(): Index
    {
        $index = $this->_createIndex();
        $mapping = new Mapping([
            'tags' => ['type' => 'keyword'],
            'title' => ['type' => 'keyword'],
            'my_join_field' => [
                'type' => 'join',
                'relations' => [
                    'question' => 'answer',
                ],
            ],
        ]);
        $index->setMapping($mapping);

        $index->addDocuments([
            new Document('1', [
                'tags' => ['linux'],
                'last_activity_date' => '2015-01-05',
                'title' => 'Question about linux #1',
            ]),
            new Document('2', [
                'tags' => ['linux'],
                'last_activity_date' => '2014-12-23',
                'title' => 'Question about linux #2',
            ]),
            new Document('3', [
                'tags' => ['windows'],
                'last_activity_date' => '2015-01-05',
                'title' => 'Question about windows #1',
            ]),
            new Document('4', [
                'tags' => ['windows'],
                'last_activity_date' => '2014-12-23',
                'title' => 'Question about windows #2',
            ]),
            new Document('5', [
                'tags' => ['osx', 'apple'],
                'last_activity_date' => '2014-12-23',
                'title' => 'Question about osx',
            ]),
        ]);

        $index->refresh();

        return $index;
    }

    protected function getOuterAggregationResult(AbstractAggregation $innerAgg): array
    {
        $outerAgg = (new Terms('top_tags'))
            ->setField('tags')
            ->setMinimumDocumentCount(2)
            ->addAggregation($innerAgg)
        ;

        $query = new Query(new MatchAll());
        $query->addAggregation($outerAgg);

        return $this->_getIndexForTest()->search($query)->getAggregation('top_tags');
    }
}
