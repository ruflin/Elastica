<?php
namespace Elastica\Test\Filter;

use Elastica\Filter\GeoBoundingBox;
use Elastica\Test\DeprecatedClassBase as BaseTest;

class GeoBoundingBoxTest extends BaseTest
{
    /**
     * @group unit
     */
    public function testDeprecated()
    {
        $reflection = new \ReflectionClass(new GeoBoundingBox('a', array(1, 2)));
        $this->assertFileDeprecated($reflection->getFileName(), 'Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html');
    }

    /**
     * @group unit
     */
    public function testAddCoordinates()
    {
        $key = 'pin.location';
        $coords = array('40.73, -74.1', '40.01, -71.12');
        $filter = new GeoBoundingBox($key, array('1,2', '3,4'));

        $filter->addCoordinates($key, $coords);
        $expectedArray = array('top_left' => $coords[0], 'bottom_right' => $coords[1]);
        $this->assertEquals($expectedArray, $filter->getParam($key));

        $returnValue = $filter->addCoordinates($key, $coords);
        $this->assertInstanceOf('Elastica\Filter\GeoBoundingBox', $returnValue);
    }

    /**
     * @group unit
     * @expectedException \Elastica\Exception\InvalidException
     */
    public function testAddCoordinatesInvalidException()
    {
        $filter = new GeoBoundingBox('foo', array());
    }

    /**
     * @group unit
     */
    public function testToArray()
    {
        $key = 'pin.location';
        $coords = array('40.73, -74.1', '40.01, -71.12');
        $filter = new GeoBoundingBox($key, $coords);

        $expectedArray = array(
            'geo_bounding_box' => array(
                $key => array(
                    'top_left' => $coords[0],
                    'bottom_right' => $coords[1],
                ),
            ),
        );

        $this->assertEquals($expectedArray, $filter->toArray());
    }
}
