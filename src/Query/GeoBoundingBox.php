<?php

declare(strict_types=1);

namespace Elastica\Query;

use Elastica\Exception\InvalidException;

/**
 * Geo bounding box query.
 *
 * @author Fabian Vogler <fabian@equivalence.ch>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-bounding-box-query.html
 */
class GeoBoundingBox extends AbstractQuery
{
    /**
     * Construct BoundingBoxQuery.
     *
     * @param array $coordinates Positional top-left/bottom-right pair or a named Elasticsearch bounding box
     */
    public function __construct(string $key, array $coordinates)
    {
        $this->addCoordinates($key, $coordinates);
    }

    /**
     * Add coordinates.
     *
     * @param array $coordinates Positional top-left/bottom-right pair or a named Elasticsearch bounding box
     *
     * @throws InvalidException If $coordinates doesn't describe a bounding box
     *
     * @return $this
     */
    public function addCoordinates(string $key, array $coordinates): self
    {
        if (isset($coordinates['top_left'], $coordinates['bottom_right'])
            || isset($coordinates['top_right'], $coordinates['bottom_left'])
            || isset($coordinates['top'], $coordinates['left'], $coordinates['bottom'], $coordinates['right'])
            || isset($coordinates['wkt'])) {
            return $this->setRawCoordinates($key, $coordinates);
        }

        if (!isset($coordinates[0], $coordinates[1])) {
            throw new InvalidException('expected $coordinates to be an array with two elements');
        }

        return $this->setRawCoordinates($key, [
            'top_left' => $coordinates[0],
            'bottom_right' => $coordinates[1],
        ]);
    }

    /**
     * Set a bounding box in Elasticsearch's native format without converting its coordinates.
     *
     * @return $this
     */
    public function setRawCoordinates(string $key, array $coordinates): self
    {
        return $this->setParam($key, $coordinates);
    }
}
