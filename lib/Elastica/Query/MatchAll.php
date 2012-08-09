<?php
/**
 * Match all query. Returns all results
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/match-all-query.html
 */
class Elastica_Query_MatchAll extends Elastica_Query_Abstract
{
    /**
     * Creates match all query
     */
    public function __construct()
    {
        $this->_params = new stdClass();
    }
}
