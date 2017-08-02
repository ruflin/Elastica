<?php
namespace Elastica\Test\Facet;

use Bonami\Elastica\Document;
use Bonami\Elastica\Facet\GeoCluster;
use Bonami\Elastica\Query;
use Bonami\Elastica\Test\Base as BaseTest;
use Bonami\Elastica\Type\Mapping;

class GeoClusterTest extends BaseTest
{
    /**
     * @group functional
     */
    public function testQuery()
    {
        $this->_checkPlugin('geocluster-facet');

        $index = $this->_createIndex();
        $type = $index->getType('testQuery');
        $geoField = 'location';

        $type->setMapping(new Mapping($type, array(
            $geoField => array('type' => 'geo_point', 'lat_lon' => true),
        )));

        $doc = new Document(1, array('name' => 'item1', 'location' => array(20, 20)));
        $type->addDocument($doc);

        $doc = new Document(2, array('name' => 'item2', 'location' => array(20, 20)));
        $type->addDocument($doc);

        $doc = new Document(3, array('name' => 'item3', 'location' => array(20, 20)));
        $type->addDocument($doc);

        $index->refresh();

        $facet = new GeoCluster('clusters');
        $facet
            ->setField($geoField)
            ->setFactor(1)
            ->setShowIds(false);
        $query = new Query();
        $query->setFacets(array($facet));

        $response = $type->search($query);
        $facets = $response->getFacets();

        $this->assertEquals(1, count($facets['clusters']['clusters']));

        $index->delete();
    }
}
