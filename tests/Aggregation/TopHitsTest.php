<?php

namespace Elastica\Test\Aggregation;

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

/**
 * @internal
 */
class TopHitsTest extends BaseAggregationTest
{
    /**
     * @group unit
     */
    public function testSetSize(): void
    {
        $agg = new TopHits('agg_name');
        $returnValue = $agg->setSize(12);
        $this->assertEquals(12, $agg->getParam('size'));
        $this->assertInstanceOf(TopHits::class, $returnValue);
    }

    /**
     * @group unit
     */
    public function testSetFrom(): void
    {
        $agg = new TopHits('agg_name');
        $returnValue = $agg->setFrom(12);
        $this->assertEquals(12, $agg->getParam('from'));
        $this->assertInstanceOf(TopHits::class, $returnValue);
    }

    /**
     * @group unit
     */
    public function testSetSort(): void
    {
        $sort = ['last_activity_date' => ['order' => 'desc']];
        $agg = new TopHits('agg_name');
        $returnValue = $agg->setSort($sort);
        $this->assertEquals($sort, $agg->getParam('sort'));
        $this->assertInstanceOf(TopHits::class, $returnValue);
    }

    /**
     * @group unit
     */
    public function testSetSource(): void
    {
        $fields = ['title', 'tags'];
        $agg = new TopHits('agg_name');
        $returnValue = $agg->setSource($fields);
        $this->assertEquals($fields, $agg->getParam('_source'));
        $this->assertInstanceOf(TopHits::class, $returnValue);
    }

    /**
     * @group unit
     */
    public function testSetVersion(): void
    {
        $agg = new TopHits('agg_name');
        $returnValue = $agg->setVersion(true);
        $this->assertTrue($agg->getParam('version'));
        $this->assertInstanceOf(TopHits::class, $returnValue);

        $agg->setVersion(false);
        $this->assertFalse($agg->getParam('version'));
    }

    /**
     * @group unit
     */
    public function testSetExplain(): void
    {
        $agg = new TopHits('agg_name');
        $returnValue = $agg->setExplain(true);
        $this->assertTrue($agg->getParam('explain'));
        $this->assertInstanceOf(TopHits::class, $returnValue);

        $agg->setExplain(false);
        $this->assertFalse($agg->getParam('explain'));
    }

    /**
     * @group unit
     */
    public function testSetHighlight(): void
    {
        $highlight = [
            'fields' => [
                'title',
            ],
        ];
        $agg = new TopHits('agg_name');
        $returnValue = $agg->setHighlight($highlight);
        $this->assertEquals($highlight, $agg->getParam('highlight'));
        $this->assertInstanceOf(TopHits::class, $returnValue);
    }

    /**
     * @group unit
     */
    public function testSetFieldDataFields(): void
    {
        $fields = ['title', 'tags'];
        $agg = new TopHits('agg_name');
        $returnValue = $agg->setFieldDataFields($fields);
        $this->assertEquals($fields, $agg->getParam('docvalue_fields'));
        $this->assertInstanceOf(TopHits::class, $returnValue);
    }

    /**
     * @group unit
     */
    public function testSetScriptFields(): void
    {
        $script = new Script('1 + 2');
        $scriptFields = new ScriptFields(['three' => $script]);

        $agg = new TopHits('agg_name');
        $returnValue = $agg->setScriptFields($scriptFields);
        $this->assertEquals($scriptFields->toArray(), $agg->getParam('script_fields')->toArray());
        $this->assertInstanceOf(TopHits::class, $returnValue);
    }

    /**
     * @group unit
     */
    public function testAddScriptField(): void
    {
        $script = new Script('2+3');
        $agg = new TopHits('agg_name');
        $returnValue = $agg->addScriptField('five', $script);
        $this->assertEquals(['five' => $script->toArray()], $agg->getParam('script_fields')->toArray());
        $this->assertInstanceOf(TopHits::class, $returnValue);
    }

    /**
     * @group functional
     */
    public function testAggregateUpdatedRecently(): void
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setSize(1);
        $aggr->setSort(['last_activity_date' => ['order' => 'desc']]);

        $resultDocs = [];
        $outerAggrResult = $this->getOuterAggregationResult($aggr);
        foreach ($outerAggrResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $resultDocs[] = $doc['_id'];
            }
        }
        $this->assertEquals([1, 3], $resultDocs);
    }

    /**
     * @group functional
     */
    public function testAggregateUpdatedFarAgo(): void
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setSize(1);
        $aggr->setSort(['last_activity_date' => ['order' => 'asc']]);

        $resultDocs = [];
        $outerAggrResult = $this->getOuterAggregationResult($aggr);
        foreach ($outerAggrResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $resultDocs[] = $doc['_id'];
            }
        }
        $this->assertEquals([2, 4], $resultDocs);
    }

    /**
     * @group functional
     */
    public function testAggregateTwoDocumentPerTag(): void
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setSize(2);

        $resultDocs = [];
        $outerAggrResult = $this->getOuterAggregationResult($aggr);
        foreach ($outerAggrResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $resultDocs[] = $doc['_id'];
            }
        }
        $this->assertEquals([1, 2, 3, 4], $resultDocs);
    }

    /**
     * @group functional
     */
    public function testAggregateTwoDocumentPerTagWithOffset(): void
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setSize(2);
        $aggr->setFrom(1);
        $aggr->setSort(['last_activity_date' => ['order' => 'desc']]);

        $resultDocs = [];
        $outerAggrResult = $this->getOuterAggregationResult($aggr);
        foreach ($outerAggrResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $resultDocs[] = $doc['_id'];
            }
        }
        $this->assertEquals([2, 4], $resultDocs);
    }

    public function limitedSourceProvider()
    {
        return [
            'string source' => ['title'],
            'array source' => [['title']],
        ];
    }

    /**
     * @group functional
     * @dataProvider limitedSourceProvider
     *
     * @param mixed $source
     */
    public function testAggregateWithLimitedSource($source): void
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setSource($source);

        $resultDocs = [];
        $outerAggrResult = $this->getOuterAggregationResult($aggr);
        foreach ($outerAggrResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $this->assertArrayHasKey('title', $doc['_source']);
                $this->assertArrayNotHasKey('tags', $doc['_source']);
                $this->assertArrayNotHasKey('last_activity_date', $doc['_source']);
            }
        }
    }

    /**
     * @group functional
     */
    public function testAggregateWithVersion(): void
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setVersion(true);

        $resultDocs = [];
        $outerAggrResult = $this->getOuterAggregationResult($aggr);
        foreach ($outerAggrResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $this->assertArrayHasKey('_version', $doc);
            }
        }
    }

    /**
     * @group functional
     */
    public function testAggregateWithExplain(): void
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setExplain(true);

        $resultDocs = [];
        $outerAggrResult = $this->getOuterAggregationResult($aggr);
        foreach ($outerAggrResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $this->assertArrayHasKey('_explanation', $doc);
            }
        }
    }

    /**
     * @group functional
     */
    public function testAggregateWithScriptFields(): void
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setSize(1);
        $aggr->setScriptFields(['three' => new Script('1 + 2')]);
        $aggr->addScriptField('five', new Script('3 + 2'));

        $resultDocs = [];
        $outerAggrResult = $this->getOuterAggregationResult($aggr);
        foreach ($outerAggrResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $this->assertEquals(3, $doc['fields']['three'][0]);
                $this->assertEquals(5, $doc['fields']['five'][0]);
            }
        }
    }

    /**
     * @group functional
     */
    public function testAggregateWithHighlight(): void
    {
        $queryString = new SimpleQueryString('linux', ['title']);

        $aggr = new TopHits('top_tag_hits');
        $aggr->setHighlight(['fields' => ['title' => new \stdClass()]]);

        $query = new Query($queryString);
        $query->addAggregation($aggr);

        $resultSet = $this->_getIndexForTest()->search($query);
        $aggrResult = $resultSet->getAggregation('top_tag_hits');

        foreach ($aggrResult['hits']['hits'] as $doc) {
            $this->assertArrayHasKey('highlight', $doc);
            $this->assertRegExp('#<em>linux</em>#', $doc['highlight']['title'][0]);
        }
    }

    /**
     * @group functional
     */
    public function testAggregateWithFieldData(): void
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setFieldDataFields(['title']);

        $query = new Query(new MatchAll());
        $query->addAggregation($aggr);

        $resultSet = $this->_getIndexForTest()->search($query);
        $aggrResult = $resultSet->getAggregation('top_tag_hits');

        foreach ($aggrResult['hits']['hits'] as $doc) {
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
            new Document(1, [
                'tags' => ['linux'],
                'last_activity_date' => '2015-01-05',
                'title' => 'Question about linux #1',
            ]),
            new Document(2, [
                'tags' => ['linux'],
                'last_activity_date' => '2014-12-23',
                'title' => 'Question about linux #2',
            ]),
            new Document(3, [
                'tags' => ['windows'],
                'last_activity_date' => '2015-01-05',
                'title' => 'Question about windows #1',
            ]),
            new Document(4, [
                'tags' => ['windows'],
                'last_activity_date' => '2014-12-23',
                'title' => 'Question about windows #2',
            ]),
            new Document(5, [
                'tags' => ['osx', 'apple'],
                'last_activity_date' => '2014-12-23',
                'title' => 'Question about osx',
            ]),
        ]);

        $index->refresh();

        return $index;
    }

    protected function getOuterAggregationResult($innerAggr)
    {
        $outerAggr = new Terms('top_tags');
        $outerAggr->setField('tags');
        $outerAggr->setMinimumDocumentCount(2);
        $outerAggr->addAggregation($innerAggr);

        $query = new Query(new MatchAll());
        $query->addAggregation($outerAggr);

        return $this->_getIndexForTest()->search($query)->getAggregation('top_tags');
    }
}
