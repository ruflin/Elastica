<?php

declare(strict_types=1);

namespace Elastica\Test\Query;

use Elastica\Query\DistanceFeature;
use Elastica\Test\Base as BaseTest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
class DistanceFeatureTest extends BaseTest
{
    #[Group('unit')]
    public function testToArrayDate(): void
    {
        $query = new DistanceFeature('field_date', 'now', '7d');

        $expectedArray = [
            'distance_feature' => [
                'field' => 'field_date',
                'origin' => 'now',
                'pivot' => '7d',
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    #[Group('unit')]
    public function testToArrayGeoPoint(): void
    {
        $query = new DistanceFeature('field_geo_point', [-71.3, 41.15], '1000m');

        $expectedArray = [
            'distance_feature' => [
                'field' => 'field_geo_point',
                'origin' => [-71.3, 41.15],
                'pivot' => '1000m',
            ],
        ];

        $this->assertEquals($expectedArray, $query->toArray());
    }

    #[Group('unit')]
    public function testSetBoost(): void
    {
        $query = new DistanceFeature('field_date', 'now', '7d');
        $query->setBoost($value = 2.0);

        $this->assertEquals($value, $query->toArray()['distance_feature']['boost']);
    }
}
