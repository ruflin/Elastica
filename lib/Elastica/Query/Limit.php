<?php

namespace Elastica\Query;

/**
 * Limit Query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-limit-query.html
 */
class Limit extends AbstractQuery
{
    /**
     * Construct limit query.
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
