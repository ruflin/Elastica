<?php

namespace Elastica\Test\Index;

use Elastica\Test\Base as BaseTest;

class StatsTest extends BaseTest
{

    public function testGetSettings()
    {
        $indexName = 'test';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create(array(), true);
        $stats = $index->getStats();
        $this->assertInstanceOf('Elastica\Index\Stats', $stats);

        $this->assertTrue($stats->get('ok'));
        $this->assertEquals(0, $stats->get('_all', 'indices', 'test', 'primaries', 'docs', 'count'));
    }
}
