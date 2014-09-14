<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\Term;
use Elastica\Test\Base as BaseTest;

class TermTest extends BaseTest
{

    public function testToArray()
    {
        $query = new Term();
        $key = 'name';
        $value = 'ruflin';
        $query->setTerm($key, $value);

        $data = $query->toArray();

        $this->assertInternalType('array', $data['term']);
        $this->assertEquals(array($key => $value), $data['term']);
    }
}
