<?php
/**
 * Implements the Geo Distance facet.
 *
 * @category Xodoa
 * @package Elastica
 * @author Gerard A. Matthew  <gerard.matthew@gmail.com>
 * @linkhttp://www.elasticsearch.org/guide/reference/api/search/facets/geo-distance-facet.html
 */
class Elastica_Facet_GeoDistance extends Elastica_Facet_Abstract
{
    /**
     * Sets the ranges for the facet all at once.
     * Sample ranges:
     * array (
     * array('to' => 50),
     * array('from' => 20, 'to' => 70),
     * array('from' => 70, 'to' => 120),
     * array('from' => 150)
     * )
     *
     * @param  array                      $ranges Numerical array with range definitions.
     * @return Elastica_Facet_GeoDistance
     */
    public function setRanges(array $ranges)
    {
        return $this->setParam ( 'ranges', $ranges );
    }

    /**
     * Set the relative GeoPoint for the facet.
     *
     * @param  string                     $typeField index type and field e.g foo.bar
     * @param  float                      $latitude
     * @param  float                      $longitude
     * @return Elastica_Facet_GeoDistance
     */
    public function setGeoPoint($typeField, $latitude, $longitude)
    {
        return $this->setParam ( $typeField, array (
                "lat" => $latitude,
                "lon" => $longitude
        ) );
    }

    /**
     * Creates the full facet definition, which includes the basic
     * facet definition of the parent.
     *
     * @see Elastica_Facet_Abstract::toArray()
     * @throws Elastica_Exception_Invalid When the right fields haven't been set.
     * @return array
     */
    public function toArray()
    {
        /**
         * Set the geo_distance in the abstract as param.
         */
        $this->_setFacetParam ( 'geo_distance', $this->_params );

        return parent::toArray ();
    }
}
