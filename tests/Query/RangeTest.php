<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Query\Range;
use Elastica\Test\Base as BaseTest;

/**
 * @internal
 */
class RangeTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testQuery(): void
    {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create([], true);

        $index->addDocuments([
            new Document(1, ['age' => 16, 'height' => 140]),
            new Document(2, ['age' => 21, 'height' => 155]),
            new Document(3, ['age' => 33, 'height' => 160]),
            new Document(4, ['age' => 68, 'height' => 160]),
        ]);

        $index->forcemerge();
        $index->refresh();

        $query = new Range('age', ['from' => 10, 'to' => 20]);
        $result = $index->search($query)->count();
        $this->assertEquals(1, $result);

        $query = new Range();
        $query->addField('height', ['gte' => 160]);

        $result = $index->search($query)->count();
        $this->assertEquals(2, $result);
    }

    /**
     * @group unit
     */
    public function testToArray(): void
    {
        $range = new Range();

        $field = ['from' => 20, 'to' => 40];
        $range->addField('age', $field);

        $expectedArray = [
            'range' => [
                'age' => $field,
            ],
        ];

        $this->assertEquals($expectedArray, $range->toArray());
    }

    /**
     * @group unit
     */
    public function testConstruct(): void
    {
        $ranges = ['from' => 20, 'to' => 40];
        $range = new Range(
            'age',
            $ranges
        );

        $expectedArray = [
            'range' => [
                'age' => $ranges,
            ],
        ];

        $this->assertEquals($expectedArray, $range->toArray());
    }
}
