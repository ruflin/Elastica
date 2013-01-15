<?php

namespace Elastica\Query;

/**
 * Nested query
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/nested-query.html
 */
class Nested extends AbstractQuery
{
    /**
     * Adds field to mlt query
     *
     * @param  string                     $path Nested object path
     * @return \Elastica\Query\Nested
     */
    public function setPath($path)
    {
        return $this->setParam('path', $path);
    }

    /**
     * Sets nested query
     *
     * @param  \Elastica\Query\AbstractQuery $query
     * @return \Elastica\Query\Nested
     */
    public function setQuery(AbstractQuery $query)
    {
        return $this->setParam('query', $query->toArray());
    }

    /**
     * Set score method
     *
     * @param  string                     $scoreMode Options: avg, total, max and none.
     * @return \Elastica\Query\Nested
     */
    public function setScoreMode($scoreMode)
    {
        return $this->setParam('score_mode', $scoreMode);
    }
}
