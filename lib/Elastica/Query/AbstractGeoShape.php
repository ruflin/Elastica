<?php

namespace Elastica\Query;

/**
 * geo_shape query.
 *
 * Query pre-indexed shape definitions
 *
 * @author Bennie Krijger <benniekrijger@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-shape-query.html
 */
abstract class AbstractGeoShape extends AbstractQuery
{
    const RELATION_INTERSECT = 'intersects';
    const RELATION_DISJOINT = 'disjoint';
    const RELATION_CONTAINS = 'within';

    /**
     * @var string
     *
     * elasticsearch path of the pre-indexed shape
     */
    protected $_path;

    /**
     * @var string
     *
     * the relation of the 2 shaped: intersects, disjoint, within
     */
    protected $_relation = self::RELATION_INTERSECT;

    /**
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
     * @return string
     */
    public function getRelation()
    {
        return $this->_relation;
    }
}
