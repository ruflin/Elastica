<?php

namespace Elastica\Filter;

use Elastica\Type as ElasticaType;

/**
 * Ids Filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Lee Parker, Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/ids-filter.html
 */
class Ids extends AbstractFilter
{
    /**
     * Creates filter object
     *
     * @param string|\Elastica\Type $type Type to filter on
     * @param array                 $ids  List of ids
     */
    public function __construct($type = null, array $ids = array())
    {
        $this->setType($type);
        $this->setIds($ids);
    }

    /**
     * Adds one more filter to the and filter
     *
     * @param  string               $id Adds id to filter
     * @return \Elastica\Filter\Ids Current object
     */
    public function addId($id)
    {
        return $this->addParam('values', $id);
    }

    /**
     * Adds one more type to query
     *
     * @param  string|\Elastica\Type $type Type name or object
     * @return \Elastica\Filter\Ids  Current object
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
     * Set type
     *
     * @param  string|\Elastica\Type $type Type name or object
     * @return \Elastica\Filter\Ids  Current object
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
     * Sets the ids to filter
     *
     * @param  array|string         $ids List of ids
     * @return \Elastica\Filter\Ids Current object
     */
    public function setIds($ids)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }

        return $this->setParam('values', $ids);
    }
}
