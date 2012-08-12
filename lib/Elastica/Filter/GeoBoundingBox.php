<?php

/**
 * Geo bounding box filter
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Fabian Vogler <fabian@equivalence.ch>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/geo-bounding-box-filter.html
 */
class Elastica_Filter_GeoBoundingBox extends Elastica_Filter_Abstract
{
    /**
     * Construct GeoBoundingBox filter
     *
     * @param string $key         Key
     * @param array  $coordinates Array with top left coordinate as first and bottom right coordinate as second element
     */
    public function __construct($key, array $coordinates)
    {
        $this->addCoordinates($key, $coordinates);
    }

    /**
     * Add coordinates
     *
     * @param  string                         $key         Key
     * @param  array                          $coordinates Array with top left coordinate as first and bottom right coordinate as second element
     * @throws Elastica_Exception_Invalid     If $coordinates doesn't have two elements
     * @return Elastica_Filter_GeoBoundingBox Current object
     */
    public function addCoordinates($key, array $coordinates)
    {
        if (!isset($coordinates[0]) || !isset($coordinates[1])) {
            throw new Elastica_Exception_Invalid('expected $coordinates to be an array with two elements');
        }

        $this->setParam($key, array(
            'top_left' => $coordinates[0],
            'bottom_right' => $coordinates[1]
        ));

        return $this;
    }
}
