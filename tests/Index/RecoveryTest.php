<?php

namespace Elastica\Test\Index;

use Elastica\Index\Recovery;
use Elastica\ResponseChecker;
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

        $this->assertTrue(ResponseChecker::isOk($recovery->getResponse()));
    }
}
