<?php

declare(strict_types=1);

namespace Elastica\Test\Index;

use Elastica\Index\Stats;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class StatsTest extends BaseTest
{
    #[Group('functional')]
    public function testGetSettings(): void
    {
        $indexName = 'test';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create([], [
            'recreate' => true,
        ]);
        $stats = $index->getStats();
        $this->assertInstanceOf(Stats::class, $stats);

        $this->assertTrue($stats->getResponse()->isOk());
        $this->assertEquals(0, $stats->get('_all', 'indices', 'test', 'primaries', 'docs', 'count'));
    }
}
