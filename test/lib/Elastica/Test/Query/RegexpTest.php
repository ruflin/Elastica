<?php

namespace Elastica\Test\Query;

use Elastica\Query\Regexp;
use Elastica\Test\Base as BaseTest;

class RegexpTest extends BaseTest
{

    public function testToArray()
    {
        $field = 'name';
        $value = 'ruf';
        $boost = 2;

        $query = new Regexp($field, $value, $boost);

        $expectedArray = array(
            'regexp' => array(
                $field => array(
                    'value' => $value,
                    'boost' => $boost,
                ),
            ),
        );

        $this->assertequals($expectedArray, $query->toArray());
    }
}
