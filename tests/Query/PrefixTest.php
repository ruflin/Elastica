<?php

namespace Elastica\Test\Query;

use Elastica\Query\Prefix;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class PrefixTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $query = new Prefix();
        $key = 'name';
        $value = 'ni';
        $boost = 2;
        $query->setPrefix($key, $value, $boost);

        $data = $query->toArray();

        $this->assertIsArray($data['prefix']);
        $this->assertIsArray($data['prefix'][$key]);
        $this->assertEquals($data['prefix'][$key]['value'], $value);
        $this->assertEquals($data['prefix'][$key]['boost'], $boost);
    }
}
