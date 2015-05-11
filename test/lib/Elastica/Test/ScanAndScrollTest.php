<?php

namespace Elastica\Test;

use Elastica\Document;
use Elastica\Query;
use Elastica\ResultSet;
use Elastica\ScanAndScroll;
use Elastica\Search;
use Elastica\Test\Base as BaseTest;

class ScanAndScrollTest extends BaseTest
{

    public function testConstruct()
    {
        $scanAndScroll = $this->_prepareScanAndScroll();

        $this->assertInstanceOf('Elastica\ScanAndScroll', $scanAndScroll);
    }

    public function testDefaultProperties()
    {
        $scanAndScroll = $this->_prepareScanAndScroll();

        $this->assertEquals('1m', $scanAndScroll->expiryTime);
        $this->assertEquals(1000, $scanAndScroll->sizePerShard);
    }

    public function testQuerySizeOverride()
    {
        $query = new Query();
        $query->setSize(100);

        $index = $this->_createIndex();
        $index->refresh();  // Waits for the index to be fully created.
        $type = $index->getType('scanAndScrollTest');

        $search = new Search($this->_getClient());
        $search->addIndex($index)->addType($type);
        $search->setQuery($query);

        $scanAndScroll = new ScanAndScroll($search);
        $scanAndScroll->sizePerShard = 10;
        $scanAndScroll->rewind();

        $this->assertEquals(10, $query->getParam('size'));
    }

    public function testSizePerShard()
    {
        $search = $this->_prepareSearch(2, 20);

        $scanAndScroll = new ScanAndScroll($search);
        $scanAndScroll->sizePerShard = 5;
        $scanAndScroll->rewind();

        $this->assertEquals(10, $scanAndScroll->current()->count());
    }

    public function testScrollId()
    {
        $search = $this->_prepareSearch(1, 2);

        $scanAndScroll = new ScanAndScroll($search);
        $scanAndScroll->sizePerShard = 1;

        $scanAndScroll->rewind();
        $this->assertEquals(
            $scanAndScroll->current()->getResponse()->getScrollId(),
            $scanAndScroll->key()
        );
    }

    public function testForeach()
    {
        $search = $this->_prepareSearch(2, 11);

        $scanAndScroll = new ScanAndScroll($search);
        $scanAndScroll->sizePerShard = 5;

        // We expect 2 scrolls:
        // 1. with 10 hits,
        // 2. with 1 hit
        // Note: there is a 3. scroll with 0 hits

        $count = 0;
        foreach ($scanAndScroll as $resultSet) {
            /** @var ResultSet $resultSet */
            $count++;

            switch (true) {
                case $count == 1: $this->assertEquals(10, $resultSet->count()); break;
                case $count == 2: $this->assertEquals(1, $resultSet->count()); break;
            }
        }

        $this->assertEquals(2, $count);
    }

    public function testScrollIgnoresSortingByDefault()
    {
        $search = $this->_prepareSearch(1, 2);
        $scanAndScroll = new ScanAndScroll($search);
        $scanAndScroll->rewind();

        $option = $search->getOption(Search::OPTION_SEARCH_TYPE);
        $this->assertNotSame(Search::OPTION_SEARCH_TYPE_SCAN, $option);
    }

    public function testSettingScrollToDoNotIgnoreSorting()
    {
        $search = $this->_prepareSearch(1, 2);
        $scanAndScroll = new ScanAndScroll($search);
        $scanAndScroll->ignoreSorting(false);
        $scanAndScroll->rewind();

        $option = $search->getOption(Search::OPTION_SEARCH_TYPE);
        $this->assertSame(Search::OPTION_SEARCH_TYPE_SCROLL, $option);
    }

    public function testSettingScrollToDoNotIgnoreSortingConstructor()
    {
        $search = $this->_prepareSearch(1, 2);
        $scanAndScroll = new ScanAndScroll($search, '1m', 1000, false);
        $scanAndScroll->rewind();

        $option = $search->getOption(Search::OPTION_SEARCH_TYPE);
        $this->assertSame(Search::OPTION_SEARCH_TYPE_SCROLL, $option);
    }

    /**
     * @param mixed $ignoreSorting
     * @dataProvider invalidIgnoreSortingProvider
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidIgnoreSortingParameters($ignoreSorting)
    {
        $scanAndScroll = $this->_prepareScanAndScroll();
        $scanAndScroll->ignoreSorting($ignoreSorting);
    }

    public function invalidIgnoreSortingProvider()
    {
        return array(
            'string' => array('invalid'),
            'array' => array(array()),
            'stdClass' => array(new \StdClass),
            'int' => array(10),
            'float' => array(5.5),
        );
    }

    private function _prepareScanAndScroll()
    {
        return new ScanAndScroll(new Search($this->_getClient()));
    }

    private function _prepareSearch($indexShards, $docs)
    {
        $index = $this->_createIndex(null, true, $indexShards);
        $type = $index->getType('scanAndScrollTest');

        $insert = array();
        for ($x = 1; $x <= $docs; $x++) {
            $insert[] = new Document($x, array('id' => $x, 'key' => 'value'));
        }

        $type->addDocuments($insert);
        $index->refresh();

        $search = new Search($this->_getClient());
        $search->addIndex($index)->addType($type);

        return $search;
    }
}
