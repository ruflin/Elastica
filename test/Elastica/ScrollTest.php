<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Document;
use Elastica\ResultSet;
use Elastica\Scroll;
use Elastica\Search;

class ScrollTest extends Base
{
    /**
     * Full foreach test.
     *
     * @group functional
     */
    public function testForeach()
    {
        $search = $this->_prepareSearch();
        $scroll = new Scroll($search);
        $count = 1;

        $this->_assertOpenSearchContexts($search->getClient(), 0);

        /** @var ResultSet $resultSet */
        foreach ($scroll as $scrollId => $resultSet) {
            $this->assertNotEmpty($scrollId);
            $this->_assertOpenSearchContexts($search->getClient(), 1);

            $results = $resultSet->getResults();
            switch (true) {
                case 1 === $count:
                    // hits: 1 - 5
                    $this->assertEquals(5, $resultSet->count());
                    $this->assertEquals('1', $results[0]->getId());
                    $this->assertEquals('5', $results[4]->getId());
                    break;
                case 2 === $count:
                    // hits: 6 - 10
                    $this->assertEquals(5, $resultSet->count());
                    $this->assertEquals('6', $results[0]->getId());
                    $this->assertEquals('10', $results[4]->getId());
                    break;
                case 3 === $count:
                    // hit: 11
                    $this->assertEquals(1, $resultSet->count());
                    $this->assertEquals('11', $results[0]->getId());
                    break;
                default:
                    $this->fail('too many iterations');
            }

            ++$count;
        }

        $this->_assertOpenSearchContexts($search->getClient(), 0);
    }

    /**
     * Scroll must not overwrite options.
     *
     * @group functional
     */
    public function testSearchRevert()
    {
        $search = $this->_prepareSearch();

        $search->setOption(Search::OPTION_SCROLL, 'must');
        $search->setOption(Search::OPTION_SCROLL_ID, 'not');
        $old = $search->getOptions();

        $scroll = new Scroll($search);

        $scroll->rewind();
        $this->assertEquals($old, $search->getOptions());

        $scroll->next();
        $this->assertEquals($old, $search->getOptions());
    }

    /**
     * Empty scroll (no results) must not run foreach.
     *
     * @group functional
     */
    public function testEmptyScroll()
    {
        $search = $this->_prepareSearch(0);
        $scroll = new Scroll($search);

        foreach ($scroll as $scrollId => $resultSet) {
            $this->fail("Empty scroll shouldn't run foreach.");
        }

        $this->assertEquals(0, $scroll->current()->count());
        $this->assertFalse($scroll->valid());

        $this->_assertOpenSearchContexts($search->getClient(), 0);
    }

    /**
     * index: 11 docs default
     * query size: 5.
     *
     * @param int $indexSize
     *
     * @return Search
     */
    private function _prepareSearch($indexSize = 11)
    {
        $index = $this->_createIndex();
        $index->refresh();
        $type = $index->getType('_doc');

        if ($indexSize > 0) {
            $docs = [];
            for ($x = 1; $x <= $indexSize; ++$x) {
                $docs[] = new Document($x, ['id' => $x, 'key' => 'value']);
            }
            $type->addDocuments($docs);
            $index->refresh();
        }

        $search = new Search($this->_getClient());
        $search->addIndex($index)->addType($type);
        $search->getQuery()->setSize(5);

        return $search;
    }

    /**
     * Tests the number of open search contexts on ES.
     *
     * @param Client $client
     * @param int    $count
     */
    private function _assertOpenSearchContexts(Client $client, $count)
    {
        $stats = $client->getStatus()->getData();
        $this->assertSame($count, $stats['_all']['total']['search']['open_contexts'], 'Open search contexts should match');
    }
}
