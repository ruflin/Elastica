<?php
namespace Elastica\Filter;

use Elastica\Type as ElasticaType;

trigger_error('Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html', E_USER_DEPRECATED);

/**
 * Ids Filter.
 *
 * @author Lee Parker, Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-ids-filter.html
 * @deprecated Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html
 */
class Ids extends AbstractFilter
{
    /**
     * Creates filter object.
     *
     * @param string|\Elastica\Type $type Type to filter on
     * @param array                 $ids  List of ids
     */
    public function __construct($type = null, array $ids = [])
    {
        $this->setType($type);
        $this->setIds($ids);
    }

    /**
     * Adds one more filter to the and filter.
     *
     * @param string $id Adds id to filter
     *
     * @return $this
     */
    public function addId($id)
    {
        return $this->addParam('values', $id);
    }

    /**
     * Adds one more type to query.
     *
     * @param string|\Elastica\Type $type Type name or object
     *
     * @return $this
     */
    public function addType($type)
    {
        if ($type instanceof ElasticaType) {
            $type = $type->getName();
        } elseif (empty($type) && !is_numeric($type)) {
            // TODO: Shouldn't this throw an exception?
            // A type can be 0, but cannot be empty
            return $this;
        }

        return $this->addParam('type', $type);
    }

    /**
     * Set type.
     *
     * @param string|\Elastica\Type $type Type name or object
     *
     * @return $this
     */
    public function setType($type)
    {
        if ($type instanceof ElasticaType) {
            $type = $type->getName();
        } elseif (empty($type) && !is_numeric($type)) {
            // TODO: Shouldn't this throw an exception or let handling of invalid params to ES?
            // A type can be 0, but cannot be empty
            return $this;
        }

        return  $this->setParam('type', $type);
    }

    /**
     * Sets the ids to filter.
     *
     * @param array|string $ids List of ids
     *
     * @return $this
     */
    public function setIds($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        return $this->setParam('values', $ids);
    }
}
