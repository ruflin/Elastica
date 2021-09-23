<?php

namespace Elastica\Test\Query;

use Elastica\Query\Term;
use Elastica\Test\Base as BaseTest;
use Yoast\PHPUnitPolyfills\Polyfills\AssertIsType;

class TermTest extends BaseTest
{
    use AssertIsType;

    /**
     * @group unit
     */
    public function testToArray()
    {
        $query = new Term();
        $key = 'name';
        $value = 'nicolas';
        $boost = 2;
        $query->setTerm($key, $value, $boost);

        $data = $query->toArray();

        self::assertIsArray($data['term']);
        self::assertIsArray($data['term'][$key]);
        $this->assertEquals($data['term'][$key]['value'], $value);
        $this->assertEquals($data['term'][$key]['boost'], $boost);
    }

    /**
     * @group unit
     */
    public function testDiacriticsValueToArray()
    {
        $query = new Term();
        $key = 'name';
        $value = 'diprè';
        $boost = 2;
        $query->setTerm($key, $value, $boost);

        $data = $query->toArray();

        self::assertIsArray($data['term']);
        self::assertIsArray($data['term'][$key]);
        $this->assertEquals($data['term'][$key]['value'], $value);
        $this->assertEquals($data['term'][$key]['boost'], $boost);
    }
}
