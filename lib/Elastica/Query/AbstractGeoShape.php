<?php
namespace Elastica\Query;

/**
 * geo_shape query.
 *
 * @author Bennie Krijger <benniekrijger@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-shape-query.html
 */
abstract class AbstractGeoShape extends AbstractQuery
{
    /** Return all documents whose geo_shape field intersects the query geometry. (default behavior) */
    const RELATION_INTERSECT = 'intersects';

    /** Return all documents whose geo_shape field has nothing in common with the query geometry. */
    const RELATION_DISJOINT = 'disjoint';

    /** Return all documents whose geo_shape field is within the query geometry. */
    const RELATION_WITHIN = 'within';

    /** Return all documents whose geo_shape field contains the query geometry. */
    const RELATION_CONTAINS = 'contains';

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
     * @param string $relation
     *
     * @return $this
     */
    public function setRelation($relation)
    {
        $this->_relation = $relation;

        return $this;
    }

    /**
     * Gets the relation of the geo_shape field and the query geometry.
     *
     * @return string
     */
    public function getRelation()
    {
        return $this->_relation;
    }
}
