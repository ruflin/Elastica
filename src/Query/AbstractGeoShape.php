<?php

namespace Elastica\Query;

/**
 * geo_shape query.
 *
 * @author Bennie Krijger <benniekrijger@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-shape-query.html
 */
abstract class AbstractGeoShape extends AbstractQuery
{
    /** Return all documents whose geo_shape field intersects the query geometry. (default behavior) */
    public const RELATION_INTERSECT = 'intersects';

    /** Return all documents whose geo_shape field has nothing in common with the query geometry. */
    public const RELATION_DISJOINT = 'disjoint';

    /** Return all documents whose geo_shape field is within the query geometry. */
    public const RELATION_WITHIN = 'within';

    /** Return all documents whose geo_shape field contains the query geometry. */
    public const RELATION_CONTAINS = 'contains';

    /**
     * Elasticsearch path of the geo_shape field.
     *
     * @var string
     */
    protected $_path;

    /**
     * @var string
     */
    protected $_relation = self::RELATION_INTERSECT;

    /**
     * Sets the relation of the geo_shape field and the query geometry.
     *
     * Possible values: intersects, disjoint, within, contains (see constants).
     *
     * @return $this
     */
    public function setRelation(string $relation): self
    {
        $this->_relation = $relation;

        return $this;
    }

    /**
     * Gets the relation of the geo_shape field and the query geometry.
     */
    public function getRelation(): string
    {
        return $this->_relation;
    }
}
