<?php
namespace Elastica\Test\Query;

use Elastica\Query\NumericRange;
use Elastica\Test\Base as BaseTest;

class NumericRangeTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testAddField()
    {
        $rangeQuery = new NumericRange();
        $returnValue = $rangeQuery->addField('fieldName', ['to' => 'value']);
        $this->assertInstanceOf(NumericRange::class, $returnValue);
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $query = new NumericRange();

        $fromTo = ['from' => 'ra', 'to' => 'ru'];
        $query->addField('name', $fromTo);

        $expectedArray = [
            'numeric_range' => [
                'name' => $fromTo,
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }
}
