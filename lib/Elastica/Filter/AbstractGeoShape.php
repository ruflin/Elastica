<?php
namespace Elastica\Filter;

trigger_error('Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html', E_USER_DEPRECATED);

/**
 * geo_shape filter.
 *
 * Filter pre-indexed shape definitions
 *
 * @author Bennie Krijger <benniekrijger@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-geo-shape-filter.html
 * @deprecated Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html
 */
abstract class AbstractGeoShape extends AbstractFilter
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
