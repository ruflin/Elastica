<?php
namespace Elastica\Filter;

use Elastica\Exception\InvalidException;

trigger_error('Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html', E_USER_DEPRECATED);

/**
 * Geo bounding box filter.
 *
 * @author Fabian Vogler <fabian@equivalence.ch>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-bounding-box-filter.html
 * @deprecated Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html
 */
class GeoBoundingBox extends AbstractFilter
{
    /**
     * Construct BoundingBoxFilter.
     *
     * @param string $key         Key
     * @param array  $coordinates Array with top left coordinate as first and bottom right coordinate as second element
     * @param array  $positions array sets the bounding box points
     */
    public function __construct($key, array $coordinates, array $positions = ['top_left', 'bottom_right'])
    {
        $this->addCoordinates($key, $coordinates, $positions);
    }

    /**
     * Add coordinates.
     *
     * @param string $key         Key
     * @param array  $coordinates Array with top left coordinate as first and bottom right coordinate as second element
     * @param array  $positions array sets the bounding box points
     *
     * @throws \Elastica\Exception\InvalidException If $coordinates doesn't have two elements
     *
     * @return $this
     */
    public function addCoordinates($key, array $coordinates, array $positions  = ['top_left', 'bottom_right'])
    {
        if (!isset($coordinates[0]) || !isset($coordinates[1])) {
            throw new InvalidException('expected $coordinates to be an array with two elements');
        }

        $this->setParam($key, array(
            $positions[0] => $coordinates[0],
            $positions[1] => $coordinates[1],
        ));

        return $this;
    }
}
