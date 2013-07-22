<?php

namespace Elastica\Test\Facet;

use Elastica\Test\Base as BaseTest;

class GeoClusterTest extends BaseTest{
    public function testQuery() {
        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create([], true);
        $type = $index->getType('testQuery');
        $geoField = 'location';

        $type->setMapping(new \Elastica\Type\Mapping($type, [
            $geoField => [ 'type' => 'geo_point', 'lat_lon' => true ]
        ]));

        $doc = new \Elastica\Document(1, ['name' => 'item1', 'location' => [20,20]]);
        $type->addDocument($doc);

        $doc = new \Elastica\Document(2, ['name' => 'item2', 'location' => [20,20]]);
        $type->addDocument($doc);

        $doc = new \Elastica\Document(3, ['name' => 'item3', 'location' => [20,20]]);
        $type->addDocument($doc);

        $index->refresh();

        $facet = new \Elastica\Facet\GeoCluster('clusters');
        $facet
            ->setField($geoField)
            ->setFactor(0.5)
            ->setShowIds(false);
        $query = new \Elastica\Query();
        $query->setFacets([$facet]);

        $response = $type->search($query);
        $facets = $response->getFacets();

        $this->assertEquals(1, count($facets['clusters']['clusters']));
    }
}