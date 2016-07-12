<?php
namespace Elastica\Query;

/**
 * Regexp query.
 *
 * @author Aurélien Le Grand <gnitg@yahoo.fr>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html
 */
class Regexp extends AbstractQuery
{
    /**
     * Construct regexp query.
     *
     * @param string $key   OPTIONAL Regexp key
     * @param string $value OPTIONAL Regexp value
     * @param float  $boost OPTIONAL Boost value (default = 1)
     */
    public function __construct($key = '', $value = null, $boost = 1.0)
    {
        if (!empty($key)) {
            $this->setValue($key, $value, $boost);
        }
    }

    /**
     * Sets the query expression for a key with its boost value.
     *
     * @param string $key
     * @param string $value
     * @param float  $boost
     *
     * @return $this
     */
    public function setValue($key, $value, $boost = 1.0)
    {
        return $this->setParam($key, ['value' => $value, 'boost' => $boost]);
    }
}
