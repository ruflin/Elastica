<?php

namespace Elastica\Test\Collapse;

use Elastica\Collapse;
use Elastica\Collapse\InnerHits;
use Elastica\Document;
use Elastica\Query;
use Elastica\Test\Base as BaseTest;
use Elastica\Type\Mapping;

class CollapseTest extends BaseTest
{
    private function _getIndexForCollapseTest()
    {
        $index = $this->_createIndex();
        $type = $index->getType('_doc');

        $mapping = new Mapping();
        $mapping->setType($type);

        $mapping->setProperties([
            'user' => ['type' => 'keyword'],
            'message' => ['type' => 'text'],
            'date' => ['type' => 'date'],
            'likes' => ['type' => 'integer'],
            'zip' => ['type' => 'keyword'],
        ]);

        $mapping->send();

        $type->addDocuments([
            new Document(1, [
                'user' => 'Veronica',
                'message' => 'Always keeping an eye on elasticsearch.',
                'date' => '2019-08-15',
                'likes' => 10,
                'zip' => '07',
            ]),
            new Document(2, [
                'user' => 'Wallace',
                'message' => 'Elasticsearch DevOps is awesome!',
                'date' => '2019-08-05',
                'likes' => 50,
                'zip' => '06',
            ]),
            new Document(3, [
                'user' => 'Logan',
                'message' => 'Can I find my lost stuff on elasticsearch?',
                'date' => '2019-08-02',
                'likes' => 1,
                'zip' => '09',
            ]),
            new Document(4, [
                'user' => 'Keith',
                'message' => 'Investigating again.',
                'date' => '2019-08-10',
                'likes' => 30,
                'zip' => '07',
            ]),
            new Document(5, [
                'user' => 'Veronica',
                'message' => 'Finding out new stuff.',
                'date' => '2019-08-01',
                'likes' => 20,
                'zip' => '07',
            ]),
            new Document(6, [
                'user' => 'Wallace',
                'message' => 'Baller.',
                'date' => '2019-08-15',
                'likes' => 20,
                'zip' => '06',
            ]),
        ]);

        $index->refresh();

        return $index;
    }

    /**
     * @group unit
     */
    public function testSetFieldName()
    {
        $collapse = new Collapse();
        $returnValue = $collapse->setFieldname('some_name');
        $this->assertEquals('some_name', $collapse->getParam('field'));
        $this->assertInstanceOf(Collapse::class, $returnValue);
    }

    /**
     * @group unit
     */
    public function testSetInnerHits()
    {
        $collapse = new Collapse();
        $innerHits = new InnerHits();
        $returnValue = $collapse->setInnerHits($innerHits);
        $this->assertEquals($innerHits, $collapse->getParam('inner_hits'));
        $this->assertInstanceOf(Collapse::class, $returnValue);
        $this->assertInstanceOf(InnerHits::class, $collapse->getParam('inner_hits'));
    }

    /**
     * @group unit
     */
    public function testSetMaxConcurrentGroupSearches()
    {
        $collapse = new Collapse();
        $returnValue = $collapse->setMaxConcurrentGroupSearches(5);
        $this->assertEquals(5, $collapse->getParam('max_concurrent_group_searches'));
        $this->assertInstanceOf(Collapse::class, $returnValue);
    }

    /**
     * @group unit
     */
    public function testAddInnerHits()
    {
        $collapse = new Collapse();

        $innerHits1 = new InnerHits();
        $innerHits1->setName('most_liked');

        $innerHits2 = new InnerHits();
        $innerHits2->setName('most_recent');

        $collapse->addInnerHits($innerHits1);
        $collapse->addInnerHits($innerHits2);

        $this->assertCount(2, $collapse->getParam('inner_hits'));
        $this->assertInternalType('array', $collapse->getParam('inner_hits'));
        $this->assertEquals($innerHits1, $collapse->getParam('inner_hits')[0]);
        $this->assertEquals($innerHits2, $collapse->getParam('inner_hits')[1]);
    }

    /**
     * @group unit
     */
    public function testSetThenAddInnerHits()
    {
        $collapse = new Collapse();

        $innerHits1 = new InnerHits();
        $innerHits1->setName('most_liked');

        $innerHits2 = new InnerHits();
        $innerHits2->setName('most_recent');

        $collapse->setInnerHits($innerHits1);
        $collapse->addInnerHits($innerHits2);

        $this->assertCount(2, $collapse->getParam('inner_hits'));
        $this->assertInternalType('array', $collapse->getParam('inner_hits'));
        $this->assertEquals($innerHits1, $collapse->getParam('inner_hits')[0]);
        $this->assertEquals($innerHits2, $collapse->getParam('inner_hits')[1]);
    }

    /**
     * @group unit
     */
    public function testSetInnerHitsOverridesExistingValue()
    {
        $collapse = new Collapse();

        $innerHits1 = new InnerHits();
        $innerHits1->setName('most_liked');

        $innerHits2 = new InnerHits();
        $innerHits2->setName('most_recent');

        $collapse->setInnerHits($innerHits1);
        $collapse->addInnerHits($innerHits2);

        $this->assertCount(2, $collapse->getParam('inner_hits'));
        $this->assertInternalType('array', $collapse->getParam('inner_hits'));
        $this->assertEquals($innerHits1, $collapse->getParam('inner_hits')[0]);
        $this->assertEquals($innerHits2, $collapse->getParam('inner_hits')[1]);

        $innerHitsOverride = new InnerHits();
        $innerHitsOverride->setName('override');

        $collapse->setInnerHits($innerHitsOverride);

        $this->assertInstanceOf(InnerHits::class, $collapse->getParam('inner_hits'));
        $this->assertEquals($innerHitsOverride, $collapse->getParam('inner_hits'));
    }

    /**
     * @group functional
     */
    public function testCollapseField()
    {
        $query = new Query();
        $query->setSource(false);
        $collapse = new Collapse();
        $query->setCollapse($collapse);

        $collapse->setFieldname('user');

        $results = $this->search($query);

        // $results->getTotalHits() isn't correct when using field collapsing, as total hits report the number of
        // documents matching a query, not the number of remaining documents after collapsing
        $this->assertCount(4, $results->getResults());

        $this->assertEquals('1', $results->getResults()[0]->getId());
        $this->assertEquals('Veronica', $results->getResults()[0]->getData()['user'][0]);

        $this->assertEquals('2', $results->getResults()[1]->getId());
        $this->assertEquals('Wallace', $results->getResults()[1]->getData()['user'][0]);

        $this->assertEquals('3', $results->getResults()[2]->getId());
        $this->assertEquals('Logan', $results->getResults()[2]->getData()['user'][0]);

        $this->assertEquals('4', $results->getResults()[3]->getId());
        $this->assertEquals('Keith', $results->getResults()[3]->getData()['user'][0]);
    }

    /**
     * @group functional
     */
    public function testCollapseWithInnerHits()
    {
        $query = new Query();
        $query->setSource(false);

        $innerHits = new InnerHits();
        $innerHits->setName('last_tweets');
        $innerHits->setSize(5);
        $innerHits->setSort(['date' => 'asc']);

        $collapse = new Collapse();
        $collapse->setFieldname('user');
        $collapse->setInnerHits($innerHits);

        $query->setCollapse($collapse);

        $results = $this->search($query);

        $this->assertCount(4, $results->getResults());

        $this->assertEquals('1', $results->getResults()[0]->getId());
        $this->assertEquals('Veronica', $results->getResults()[0]->getData()['user'][0]);
        $this->assertEquals('2', $results->getResults()[0]->getInnerHits()['last_tweets']['hits']['total']['value']);
        $this->assertEquals('Finding out new stuff.',
            $results->getResults()[0]->getInnerHits()['last_tweets']['hits']['hits'][0]['_source']['message']);
        $this->assertEquals('Always keeping an eye on elasticsearch.',
            $results->getResults()[0]->getInnerHits()['last_tweets']['hits']['hits'][1]['_source']['message']);
    }

    /**
     * @group functional
     */
    public function testCollapseWithMultipleInnerHits()
    {
        $query = new Query();
        $query->setSource(false);

        $innerHitsLiked = new InnerHits();
        $innerHitsLiked->setName('most_liked');
        $innerHitsLiked->setSize(5);
        $innerHitsLiked->setSort(['likes']);

        $innerHitsRecent = new InnerHits();
        $innerHitsRecent->setName('most_recent');
        $innerHitsRecent->setSize(5);
        $innerHitsRecent->setSort(['date' => 'asc']);

        $collapse = new Collapse();
        $collapse->setFieldname('user');
        $collapse->addInnerHits($innerHitsLiked);
        $collapse->addInnerHits($innerHitsRecent);

        $query->setCollapse($collapse);

        $results = $this->search($query);

        $this->assertCount(4, $results->getResults());

        $this->assertEquals('1', $results->getResults()[0]->getId());
        $this->assertEquals('Veronica', $results->getResults()[0]->getData()['user'][0]);

        $this->assertEquals('2', $results->getResults()[0]->getInnerHits()['most_liked']['hits']['total']['value']);
        $this->assertEquals('Always keeping an eye on elasticsearch.',
            $results->getResults()[0]->getInnerHits()['most_liked']['hits']['hits'][0]['_source']['message']);
        $this->assertEquals('Finding out new stuff.',
            $results->getResults()[0]->getInnerHits()['most_liked']['hits']['hits'][1]['_source']['message']);

        $this->assertEquals('2', $results->getResults()[0]->getInnerHits()['most_recent']['hits']['total']['value']);
        $this->assertEquals('Finding out new stuff.',
            $results->getResults()[0]->getInnerHits()['most_recent']['hits']['hits'][0]['_source']['message']);
        $this->assertEquals('Always keeping an eye on elasticsearch.',
            $results->getResults()[0]->getInnerHits()['most_recent']['hits']['hits'][1]['_source']['message']);
    }

    /**
     * @group functional
     */
    public function testSecondLevelCollapsing()
    {
        $query = new Query();
        $query->setSource(false);

        $innerHitsByZip = new InnerHits();
        $innerHitsByZip->setName('by_zip');
        $innerHitsByZip->setSize(5);
        $innerHitsByZip->setSource(false);

        $collapse = new Collapse();
        $collapse->setFieldname('zip');
        $collapse->setInnerHits($innerHitsByZip);

        $nestedCollapse = new Collapse();
        $nestedCollapse->setFieldname('user');

        $innerHitsByZip->setCollapse($nestedCollapse);

        $query->setCollapse($collapse);

        $results = $this->search($query);

        $this->assertCount(3, $results->getResults());

        $this->assertEquals('07', $results->getResults()[0]->getData()['zip'][0]);
        $this->assertCount(2, $results->getResults()[0]->getInnerHits()['by_zip']['hits']['hits']);
        $this->assertEquals('Veronica', $results->getResults()[0]->getInnerHits()['by_zip']['hits']['hits'][0]['fields']['user'][0]);
        $this->assertEquals('Keith', $results->getResults()[0]->getInnerHits()['by_zip']['hits']['hits'][1]['fields']['user'][0]);
    }

    /**
     * @param Query $query
     *
     * @return \Elastica\ResultSet
     */
    private function search(Query $query)
    {
        return $this->_getIndexForCollapseTest()->getType('_doc')->search($query);
    }
}
