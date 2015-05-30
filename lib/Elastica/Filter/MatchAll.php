<?php
namespace Elastica\Filter;

/**
 * Match all filter.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-all-filter.html
 */
class MatchAll extends AbstractFilter
{
    /**
     * Creates match all filter.
     */
    public function __construct()
    {
        $this->_params = new \stdClass();
    }
}
