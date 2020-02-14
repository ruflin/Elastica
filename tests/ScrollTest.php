<?php

namespace Elastica\Test;

use Elastica\Client;
use Elastica\Document;
use Elastica\ResultSet;
use Elastica\Scroll;
use Elastica\Search;

/**
 * @internal
 */
class ScrollTest extends Base
{
    /**
     * Full foreach test.
     *
     * @group functional
     */
    public function testForeach(): void
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
    public function testSearchRevert(): void
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
    public function testEmptyScroll(): void
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
     * Test with ignore_unavailable search option.
     *
     * @group functional
     */
    public function testScrollWithIgnoreUnavailable(): void
    {
        $search = $this->_prepareSearch();
        $search->addIndex('unavailable_index');
        $search->setOption($search::OPTION_SEARCH_IGNORE_UNAVAILABLE, 'true');
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
     * index: 11 docs default
     * query size: 5.
     */
    private function _prepareSearch(int $indexSize = 11): Search
    {
        $index = $this->_createIndex();
        $index->refresh();

        if ($indexSize > 0) {
            $docs = [];
            for ($x = 1; $x <= $indexSize; ++$x) {
                $docs[] = new Document($x, ['id' => $x, 'key' => 'value']);
            }
            $index->addDocuments($docs);
            $index->refresh();
        }

        $search = new Search($this->_getClient());
        $search->addIndex($index);
        $search->getQuery()->setSize(5);

        return $search;
    }

    /**
     * Tests the number of open search contexts on ES.
     */
    private function _assertOpenSearchContexts(Client $client, int $count): void
    {
        $stats = $client->getStatus()->getData();
        $this->assertSame($count, $stats['_all']['total']['search']['open_contexts'], 'Open search contexts should match');
    }
}
