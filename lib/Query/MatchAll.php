<?php

namespace Elastica\Query;

/**
 * Match all query. Returns all results
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/match-all-query.html
 */
class MatchAll extends AbstractQuery
{
    /**
     * Creates match all query
     */
    public function __construct()
    {
        $this->_params = new \stdClass();
    }
}
