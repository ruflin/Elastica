<?php
namespace Elastica\Facet;

/**
 * Implements the Geo Distance facet.
 *
 * @author Gerard A. Matthew  <gerard.matthew@gmail.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/search-facets-geo-distance-facet.html
 * @deprecated Facets are deprecated and will be removed in a future release. You are encouraged to migrate to aggregations instead.
 */
class GeoDistance extends AbstractFacet
{
    /**
     * Sets the ranges for the facet all at once.
     * Sample ranges:
     * array (
     * array('to' => 50),
     * array('from' => 20, 'to' => 70),
     * array('from' => 70, 'to' => 120),
     * array('from' => 150)
     * ).
     *
     * @param array $ranges Numerical array with range definitions.
     *
     * @return $this
     */
    public function setRanges(array $ranges)
    {
        return $this->setParam('ranges', $ranges);
    }

    /**
     * Set the relative GeoPoint for the facet.
     *
     * @param string $typeField index type and field e.g foo.bar
     * @param float  $latitude
     * @param float  $longitude
     *
     * @return $this
     */
    public function setGeoPoint($typeField, $latitude, $longitude)
    {
        return $this->setParam($typeField, array(
            'lat' => $latitude,
            'lon' => $longitude,
        ));
    }

    /**
     * Creates the full facet definition, which includes the basic
     * facet definition of the parent.
     *
     * @see \Elastica\Facet\AbstractFacet::toArray()
     *
     * @throws \Elastica\Exception\InvalidException When the right fields haven't been set.
     *
     * @return array
     */
    public function toArray()
    {
        /*
         * Set the geo_distance in the abstract as param.
         */
        $this->_setFacetParam('geo_distance', $this->_params);

        return parent::toArray();
    }
}
