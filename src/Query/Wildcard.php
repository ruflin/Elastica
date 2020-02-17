<?php

namespace Elastica\Query;

/**
 * Wildcard query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-wildcard-query.html
 */
class Wildcard extends AbstractQuery
{
    /**
     * Construct wildcard query.
     *
     * @param string $key   OPTIONAL Wildcard key
     * @param string $value OPTIONAL Wildcard value
     * @param float  $boost OPTIONAL Boost value (default = 1)
     */
    public function __construct(string $key = '', ?string $value = null, float $boost = 1.0)
    {
        if (!empty($key)) {
            $this->setValue($key, $value, $boost);
        }
    }

    /**
     * Sets the query expression for a key with its boost value.
     *
     * @return $this
     */
    public function setValue(string $key, string $value, float $boost = 1.0): self
    {
        return $this->setParam($key, ['value' => $value, 'boost' => $boost]);
    }
}
