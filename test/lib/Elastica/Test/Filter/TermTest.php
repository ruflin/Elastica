<?php

namespace Elastica\Test\Filter;

use Elastica\Filter\TermFilter;
use Elastica\Test\Base as BaseTest;

class TermsTest extends BaseTest
{

    public function testToArray()
    {
        $query = new TermFilter();
        $key = 'name';
        $value = 'ruflin';
        $query->setTerm($key, $value);

        $data = $query->toArray();

        $this->assertInternalType('array', $data['term']);
        $this->assertEquals(array($key => $value), $data['term']);
    }
}
