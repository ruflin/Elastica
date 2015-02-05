<?php

namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\Terms;
use Elastica\Aggregation\TopHits;
use Elastica\Document;
use Elastica\Query;
use Elastica\Query\MatchAll;
use Elastica\Query\SimpleQueryString;
use Elastica\Script;
use Elastica\ScriptFields;

class TopHitsTest extends BaseAggregationTest
{
    protected $_index;

    public function setUp()
    {
        parent::setUp();
        $this->_index = $this->_createIndex();

        $docs = array(
            new Document(1, array(
                'tags' => array('linux'),
                'last_activity_date' => '2015-01-05',
                'title' => 'Question about linux #1',
            )),
            new Document(2, array(
                'tags' => array('linux'),
                'last_activity_date' => '2014-12-23',
                'title' => 'Question about linux #2',
            )),
            new Document(3, array(
                'tags' => array('windows'),
                'last_activity_date' => '2015-01-05',
                'title' => 'Question about windows #1',
            )),
            new Document(4, array(
                'tags' => array('windows'),
                'last_activity_date' => '2014-12-23',
                'title' => 'Question about windows #2',
            )),
            new Document(5, array(
                'tags' => array('osx', 'apple'),
                'last_activity_date' => '2014-12-23',
                'title' => 'Question about osx',
            )),
        );

        $this->_index->getType('questions')->addDocuments($docs);
        $this->_index->refresh();
    }

    public function testSetSize()
    {
        $agg = new TopHits('agg_name');
        $returnValue = $agg->setSize(12);
        $this->assertEquals(12, $agg->getParam('size'));
        $this->assertInstanceOf('Elastica\Aggregation\TopHits', $returnValue);
    }

    public function testSetFrom()
    {
        $agg = new TopHits('agg_name');
        $returnValue = $agg->setFrom(12);
        $this->assertEquals(12, $agg->getParam('from'));
        $this->assertInstanceOf('Elastica\Aggregation\TopHits', $returnValue);
    }

    public function testSetSort()
    {
        $sort = array('last_activity_date' => array('order' => 'desc'));
        $agg = new TopHits('agg_name');
        $returnValue = $agg->setSort($sort);
        $this->assertEquals($sort, $agg->getParam('sort'));
        $this->assertInstanceOf('Elastica\Aggregation\TopHits', $returnValue);
    }

    public function testSetSource()
    {
        $fields = array('title', 'tags');
        $agg = new TopHits('agg_name');
        $returnValue = $agg->setSource($fields);
        $this->assertEquals($fields, $agg->getParam('_source'));
        $this->assertInstanceOf('Elastica\Aggregation\TopHits', $returnValue);
    }

    public function testSetVersion()
    {
        $agg = new TopHits('agg_name');
        $returnValue = $agg->setVersion(true);
        $this->assertTrue($agg->getParam('version'));
        $this->assertInstanceOf('Elastica\Aggregation\TopHits', $returnValue);

        $agg->setVersion(false);
        $this->assertFalse($agg->getParam('version'));
    }

    public function testSetExplain()
    {
        $agg = new TopHits('agg_name');
        $returnValue = $agg->setExplain(true);
        $this->assertTrue($agg->getParam('explain'));
        $this->assertInstanceOf('Elastica\Aggregation\TopHits', $returnValue);

        $agg->setExplain(false);
        $this->assertFalse($agg->getParam('explain'));
    }

    public function testSetHighlight()
    {
        $highlight = array(
            'fields' => array(
                'title',
            ),
        );
        $agg = new TopHits('agg_name');
        $returnValue = $agg->setHighlight($highlight);
        $this->assertEquals($highlight, $agg->getParam('highlight'));
        $this->assertInstanceOf('Elastica\Aggregation\TopHits', $returnValue);
    }

    public function testSetFieldDataFields()
    {
        $fields = array('title', 'tags');
        $agg = new TopHits('agg_name');
        $returnValue = $agg->setFieldDataFields($fields);
        $this->assertEquals($fields, $agg->getParam('fielddata_fields'));
        $this->assertInstanceOf('Elastica\Aggregation\TopHits', $returnValue);
    }

    public function testSetScriptFields()
    {
        $script = new Script('1 + 2');
        $scriptFields = new ScriptFields(array('three' => $script));

        $agg = new TopHits('agg_name');
        $returnValue = $agg->setScriptFields($scriptFields);
        $this->assertEquals($scriptFields->toArray(), $agg->getParam('script_fields'));
        $this->assertInstanceOf('Elastica\Aggregation\TopHits', $returnValue);
    }

    public function testAddScriptField()
    {
        $script = new Script('2+3');
        $agg = new TopHits('agg_name');
        $returnValue = $agg->addScriptField('five', $script);
        $this->assertEquals(array('five' => $script->toArray()), $agg->getParam('script_fields'));
        $this->assertInstanceOf('Elastica\Aggregation\TopHits', $returnValue);
    }

    protected function getOuterAggregationResult($innerAggr)
    {
        $outerAggr = new Terms('top_tags');
        $outerAggr->setField('tags');
        $outerAggr->setMinimumDocumentCount(2);
        $outerAggr->addAggregation($innerAggr);

        $query = new Query(new MatchAll());
        $query->addAggregation($outerAggr);

        return $this->_index->search($query)->getAggregation('top_tags');
    }

    public function testAggregateUpdatedRecently()
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setSize(1);
        $aggr->setSort(array('last_activity_date' => array('order' => 'desc')));

        $resultDocs = array();
        $outerAggrResult = $this->getOuterAggregationResult($aggr);
        foreach ($outerAggrResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $resultDocs[] = $doc['_id'];
            }
        }
        $this->assertEquals(array(1, 3), $resultDocs);
    }

    public function testAggregateUpdatedFarAgo()
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setSize(1);
        $aggr->setSort(array('last_activity_date' => array('order' => 'asc')));

        $resultDocs = array();
        $outerAggrResult = $this->getOuterAggregationResult($aggr);
        foreach ($outerAggrResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $resultDocs[] = $doc['_id'];
            }
        }
        $this->assertEquals(array(2, 4), $resultDocs);
    }

    public function testAggregateTwoDocumentPerTag()
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setSize(2);

        $resultDocs = array();
        $outerAggrResult = $this->getOuterAggregationResult($aggr);
        foreach ($outerAggrResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $resultDocs[] = $doc['_id'];
            }
        }
        $this->assertEquals(array(1, 2, 3, 4), $resultDocs);
    }

    public function testAggregateTwoDocumentPerTagWithOffset()
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setSize(2);
        $aggr->setFrom(1);
        $aggr->setSort(array('last_activity_date' => array('order' => 'desc')));

        $resultDocs = array();
        $outerAggrResult = $this->getOuterAggregationResult($aggr);
        foreach ($outerAggrResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $resultDocs[] = $doc['_id'];
            }
        }
        $this->assertEquals(array(2, 4), $resultDocs);
    }

    public function testAggregateWithLimitedSource()
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setSource(array('title'));

        $resultDocs = array();
        $outerAggrResult = $this->getOuterAggregationResult($aggr);
        foreach ($outerAggrResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $this->assertArrayHasKey('title', $doc['_source']);
                $this->assertArrayNotHasKey('tags', $doc['_source']);
                $this->assertArrayNotHasKey('last_activity_date', $doc['_source']);
            }
        }
    }

    public function testAggregateWithVersion()
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setVersion(true);

        $resultDocs = array();
        $outerAggrResult = $this->getOuterAggregationResult($aggr);
        foreach ($outerAggrResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $this->assertArrayHasKey('_version', $doc);
            }
        }
    }

    public function testAggregateWithExplain()
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setExplain(true);

        $resultDocs = array();
        $outerAggrResult = $this->getOuterAggregationResult($aggr);
        foreach ($outerAggrResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $this->assertArrayHasKey('_explanation', $doc);
            }
        }
    }

    public function testAggregateWithScriptFields()
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setSize(1);
        $aggr->setScriptFields(array('three' => new Script('1 + 2')));
        $aggr->addScriptField('five', new Script('3 + 2'));

        $resultDocs = array();
        $outerAggrResult = $this->getOuterAggregationResult($aggr);
        foreach ($outerAggrResult['buckets'] as $bucket) {
            foreach ($bucket['top_tag_hits']['hits']['hits'] as $doc) {
                $this->assertEquals(3, $doc['fields']['three'][0]);
                $this->assertEquals(5, $doc['fields']['five'][0]);
            }
        }
    }

    public function testAggregateWithHighlight()
    {
        $queryString = new SimpleQueryString('linux', array('title'));

        $aggr = new TopHits('top_tag_hits');
        $aggr->setHighlight(array('fields' => array('title' => new \stdClass())));

        $query = new Query($queryString);
        $query->addAggregation($aggr);

        $resultSet = $this->_index->search($query);
        $aggrResult = $resultSet->getAggregation('top_tag_hits');

        foreach ($aggrResult['hits']['hits'] as $doc) {
            $this->assertArrayHasKey('highlight', $doc);
            $this->assertRegExp('#<em>linux</em>#', $doc['highlight']['title'][0]);
        }
    }

    public function testAggregateWithFieldData()
    {
        $aggr = new TopHits('top_tag_hits');
        $aggr->setFieldDataFields(array('title'));

        $query = new Query(new MatchAll());
        $query->addAggregation($aggr);

        $resultSet = $this->_index->search($query);
        $aggrResult = $resultSet->getAggregation('top_tag_hits');

        foreach ($aggrResult['hits']['hits'] as $doc) {
            $this->assertArrayHasKey('fields', $doc);
            $this->assertArrayHasKey('title', $doc['fields']);
            $this->assertArrayNotHasKey('tags', $doc['fields']);
            $this->assertArrayNotHasKey('last_activity_date', $doc['fields']);
        }
    }
}
