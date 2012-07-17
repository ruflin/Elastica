<?php
/**
 * Nested query
 *
 * @uses Elastica_Query_Abstract
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/nested-query.html
 */
class Elastica_Query_Nested extends Elastica_Query_Abstract
{
    /**
     * Adds field to mlt query
     *
     * @param  string                $path Nested object path
     * @return Elastica_Query_Nested
     */
    public function setPath($path)
    {
        return $this->setParam('path', $path);
    }

    /**
     * Sets nested query
     *
     * @param  Elastica_Query_Abstract $query
     * @return Elastica_Query_Nested
     */
    public function setQuery(Elastica_Query_Abstract $query)
    {
        return $this->setParam('query', $query->toArray());
    }

    /**
     * Set score method
     *
     * @param  string                $scoreMode Options: avg, total, max and none.
     * @return Elastica_Query_Nested
     */
    public function setScoreMode($scoreMode)
    {
        return $this->setParam('score_mode', $scoreMode);
    }
}
