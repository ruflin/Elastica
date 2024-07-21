<?php

declare(strict_types=1);

namespace Elastica\Test;

use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class PointInTimeTest extends BaseTest
{
    #[Group('functional')]
    public function testOpenClosePointInTime(): void
    {
        $index = $this->_createIndex();
        $pitOpenResponse = $index->openPointInTime('10s');
        $this->assertTrue($pitOpenResponse->isOk());

        $pitId = $pitOpenResponse->getData()['id'];

        $client = $index->getClient();
        $pitCloseResponse = $client->closePointInTime($pitId);
        $this->assertTrue($pitCloseResponse->isOk());

        $this->assertArrayHasKey('num_freed', $pitCloseResponse->getData());
    }
}
