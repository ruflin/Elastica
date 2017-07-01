<?php
namespace Elastica\Test\Query;

use Elastica\Query\GeohashCell;
use Elastica\Test\DeprecatedClassBase;

class GeohashCellTest extends DeprecatedClassBase
{
    /**
     * @group unit
     */
    public function testToArray()
    {
        $query = new GeohashCell('pin', ['lat' => 37.789018, 'lon' => -122.391506], '50m');
        $expected = [
            'geohash_cell' => [
                'pin' => [
                    'lat' => 37.789018,
                    'lon' => -122.391506,
                ],
                'precision' => '50m',
                'neighbors' => false,
            ],
        ];
        $this->assertEquals($expected, $query->toArray());
    }
}
