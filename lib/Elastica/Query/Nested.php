<?php
namespace Elastica\Query;

/**
 * Nested query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-nested-query.html
 */
class Nested extends AbstractQuery
{
    /**
     * Adds field to mlt query.
     *
     * @param string $path Nested object path
     *
     * @return $this
     */
    public function setPath($path)
    {
        return $this->setParam('path', $path);
    }

    /**
     * Sets nested query.
     *
     * @param \Elastica\Query\AbstractQuery $query
     *
     * @return $this
     */
    public function setQuery(AbstractQuery $query)
    {
        return $this->setParam('query', $query);
    }

    /**
     * Set score method.
     *
     * @param string $scoreMode Options: avg, total, max and none.
     *
     * @return $this
     */
    public function setScoreMode($scoreMode)
    {
        return $this->setParam('score_mode', $scoreMode);
    }
}
