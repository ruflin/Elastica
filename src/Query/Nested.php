<?php

namespace Elastica\Query;

/**
 * Nested query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-nested-query.html
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
    public function setPath(string $path): self
    {
        return $this->setParam('path', $path);
    }

    /**
     * Sets nested query.
     *
     * @return $this
     */
    public function setQuery(AbstractQuery $query): self
    {
        return $this->setParam('query', $query);
    }

    /**
     * Set score method.
     *
     * @param string $scoreMode options: avg, total, max and none
     *
     * @return $this
     */
    public function setScoreMode(string $scoreMode = 'avg'): self
    {
        return $this->setParam('score_mode', $scoreMode);
    }

    /**
     * Set inner hits.
     *
     * @return $this
     */
    public function setInnerHits(InnerHits $innerHits): self
    {
        return $this->setParam('inner_hits', $innerHits);
    }
}
