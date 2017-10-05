<?php
namespace Elastica\Query;

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
     * Creates filter object.
     *
     * @param array $ids List of ids
     */
    public function __construct(array $ids = [])
    {
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
            $this->_params['values'] = [$ids];
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
        return ['ids' => $this->_params];
    }
}
