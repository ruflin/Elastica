<?php

namespace Elastica\Query;

/**
 * Match all query. Returns all results.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-all-query.html
 */
class MatchAll extends AbstractQuery
{
    /**
     * Creates match all query.
     */
    public function __construct()
    {
        $this->_params = new \stdClass();
    }
}
