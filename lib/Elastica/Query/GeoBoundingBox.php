<?php
namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * Geo bounding box query.
 *
 * @author Fabian Vogler <fabian@equivalence.ch>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-bounding-box-query.html
 */
class GeoBoundingBox extends AbstractQuery
{
    /**
     * Construct BoundingBoxQuery.
     *
     * @param string $key         Key
     * @param array  $coordinates Array with top left coordinate as first and bottom right coordinate as second element
     */
    public function __construct($key, array $coordinates)
    {
        $this->addCoordinates($key, $coordinates);
    }

    /**
     * Add coordinates.
     *
     * @param string $key         Key
     * @param array  $coordinates Array with top left coordinate as first and bottom right coordinate as second element
     *
     * @throws \Elastica\Exception\InvalidException If $coordinates doesn't have two elements
     *
     * @return $this
     */
    public function addCoordinates($key, array $coordinates)
    {
        if (!isset($coordinates[0]) || !isset($coordinates[1])) {
            throw new InvalidException('expected $coordinates to be an array with two elements');
        }

        $this->setParam($key, [
            'top_left' => $coordinates[0],
            'bottom_right' => $coordinates[1],
        ]);

        return $this;
    }
}
