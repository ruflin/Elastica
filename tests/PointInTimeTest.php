<?php

namespace Elastica\Test;

use Elastica\ResponseChecker;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class PointInTimeTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testOpenClosePointInTime(): void
    {
        $index = $this->_createIndex();
        $pitOpenResponse = $index->openPointInTime('10s');
        $this->assertTrue(ResponseChecker::isOk($pitOpenResponse));

        $pitId = $pitOpenResponse->asArray()['id'];

        $client = $index->getClient();
        $pitCloseResponse = $client->closePointInTime($pitId);
        $this->assertTrue(ResponseChecker::isOk($pitCloseResponse));

        $this->assertArrayHasKey('num_freed', $pitCloseResponse->asArray());
    }
}
