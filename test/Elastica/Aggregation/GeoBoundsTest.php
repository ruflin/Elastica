<?php
namespace Elastica\Test\Aggregation;

use Elastica\Aggregation\GeoBounds;
use Elastica\Document;
use Elastica\Query;
use Elastica\Type\Mapping;

class GeoBoundsTest extends BaseAggregationTest
{
    private function getIndexForTest()
    {
        $index = $this->_createIndex();
        $type = $index->getType('test');

        $type->setMapping(new Mapping(null, [
            'location' => ['type' => 'geo_point'],
        ]));

        $type->addDocuments([
            new Document(1, ['location' => ['lat' => 32.849437, 'lon' => -117.271732]]),
            new Document(2, ['location' => ['lat' => 32.798320, 'lon' => -117.246648]]),
            new Document(3, ['location' => ['lat' => 37.782439, 'lon' => -122.392560]]),
        ]);

        $index->refresh();

        return $index;
    }

    /**
     * @group functional
     */
    public function testGeoBoundsAggregation()
    {
        $agg = new GeoBounds('viewport', 'location');

        $query = new Query();
        $query->addAggregation($agg);
        $results = $this->getIndexForTest()->search($query)->getAggregation('viewport');

        $this->assertEquals(37.782438984141, $results['bounds']['top_left']['lat']);
        $this->assertEquals(-122.39256000146, $results['bounds']['top_left']['lon']);
        $this->assertEquals(32.798319971189, $results['bounds']['bottom_right']['lat']);
        $this->assertEquals(-117.24664804526, $results['bounds']['bottom_right']['lon']);
    }
}
