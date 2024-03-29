<?php

declare(strict_types=1);

namespace Elastica\Test\Index;

use Elastica\Index\Recovery;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class RecoveryTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testGetSettings(): void
    {
        $indexName = 'test';

        $client = $this->_getClient();
        $index = $client->getIndex($indexName);
        $index->create([], [
            'recreate' => true,
        ]);
        $recovery = $index->getRecovery();
        $this->assertInstanceOf(Recovery::class, $recovery);

        $this->assertTrue($recovery->getResponse()->isOk());
    }
}
