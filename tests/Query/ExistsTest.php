<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Query\Exists;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class ExistsTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $field = 'test';
        $query = new Exists($field);

        $expectedArray = ['exists' => ['field' => $field]];
        $this->assertEquals($expectedArray, $query->toArray());
    }

    /**
     * @group unit
     */
    public function testSetField(): void
    {
        $field = 'test';
        $query = new Exists($field);

        $this->assertSame($field, $query->getParam('field'));

        $newField = 'hello world';
        $query->setField($newField);

        $this->assertEquals($newField, $query->getParam('field'));
    }
}
