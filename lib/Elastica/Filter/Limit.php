<?php
namespace Elastica\Filter;

/**
 * Limit Filter.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-limit-filter.html
 */
class Limit extends AbstractFilter
{
    /**
     * Construct limit filter.
     *
     * @param int $limit Limit
     */
    public function __construct($limit)
    {
        $this->setLimit($limit);
    }

    /**
     * Set the limit.
     *
     * @param int $limit Limit
     *
     * @return $this
     */
    public function setLimit($limit)
    {
        return $this->setParam('value', (int) $limit);
    }
}
