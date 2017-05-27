<?php
namespace Elastica\Query;

/**
 * SpanTerm query.
 *
 * @author Marek Hernik <marek.hernik@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-span-term-query.html
 */
class SpanTerm extends SpanQuery
{
    /**
     * Constructs the SpanTerm query object.
     *
     * @param string $field
     * @param string $value
     * @param float  $boost OPTIONAL Boost value (default = 1)
     */
    public function __construct($field, $value, $boost = 1.0)
    {
        $this->setRawTerm($field, $value, $boost);
    }

    /**
     * Sets the query expression for a key with its boost value.
     *
     * @param string $field
     * @param string $value
     * @param float  $boost
     *
     * @return $this
     */
    public function setRawTerm($field, $value, $boost = 1.0)
    {
        return $this->setParam($field, ['value' => $value, 'boost' => $boost]);
    }
}