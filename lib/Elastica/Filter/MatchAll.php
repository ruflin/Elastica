<?php

namespace Elastica\Filter;

/**
 * Match all filter
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/match-all-filter.html
 */
class MatchAll extends AbstractFilter
{
    /**
     * Creates match all filter
     */
    public function __construct()
    {
        $this->_params = new \stdClass();
    }
}
