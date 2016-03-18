<?php

namespace Elastica\Query;

use Elastica\Type;

/**
 * Ids Query.
 *
 * @author Lee Parker
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @author Tim Rupp
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-ids-query.html
 */
class Ids extends AbstractQuery
{
    /**
     * Params.
     *
     * @var array Params
     */
    protected $_params = array();

    /**
     * Creates filter object.
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
     * Adds one more filter to the and filter.
     *
     * @param string $id Adds id to filter
     *
     * @return $this
     */
    public function addId($id)
    {
        $this->_params['values'][] = $id;

        return $this;
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
        if ($type instanceof Type) {
            $type = $type->getName();
        } elseif (empty($type) && !is_numeric($type)) {
            // A type can be 0, but cannot be empty
            return $this;
        }

        $this->_params['type'][] = $type;

        return $this;
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
        if ($type instanceof Type) {
            $type = $type->getName();
        } elseif (empty($type) && !is_numeric($type)) {
            // A type can be 0, but cannot be empty
            return $this;
        }

        $this->_params['type'] = $type;

        return $this;
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
        if (is_array($ids)) {
            $this->_params['values'] = $ids;
        } else {
            $this->_params['values'] = array($ids);
        }

        return $this;
    }

    /**
     * Converts filter to array.
     *
     * @see \Elastica\Query\AbstractQuery::toArray()
     *
     * @return array Query array
     */
    public function toArray()
    {
        return array('ids' => $this->_params);
    }
}
