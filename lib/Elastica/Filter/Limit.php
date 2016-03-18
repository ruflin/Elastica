<?php

namespace Elastica\Filter;

trigger_error('Deprecated: Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html', E_USER_DEPRECATED);

/**
 * Limit Filter.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-limit-filter.html
 * @deprecated Filters are deprecated. Use queries in filter context. See https://www.elastic.co/guide/en/elasticsearch/reference/2.0/query-dsl-filters.html
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
