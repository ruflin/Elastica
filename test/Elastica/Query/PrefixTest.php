<?php

namespace Elastica\Test\Query;

use Elastica\Query\Prefix;
use Elastica\Test\Base as BaseTest;
use Yoast\PHPUnitPolyfills\Polyfills\AssertIsType;

class PrefixTest extends BaseTest
{
    use AssertIsType;

    /**
     * @group unit
     */
    public function testToArray()
    {
        $query = new Prefix();
        $key = 'name';
        $value = 'ni';
        $boost = 2;
        $query->setPrefix($key, $value, $boost);

        $data = $query->toArray();

        self::assertIsArray($data['prefix']);
        self::assertIsArray($data['prefix'][$key]);
        $this->assertEquals($data['prefix'][$key]['value'], $value);
        $this->assertEquals($data['prefix'][$key]['boost'], $boost);
    }
}
