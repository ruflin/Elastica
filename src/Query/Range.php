<?php

namespace Elastica\Query;

/**
 * Range query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-range-query.html
 */
class Range extends AbstractQuery
{
    /**
     * Constructor.
     *
     * @param string $fieldName Field name
     * @param array  $args      Field arguments
     */
    public function __construct(?string $fieldName = null, array $args = [])
    {
        if ($fieldName) {
            $this->addField($fieldName, $args);
        }
    }

    /**
     * Adds a range field to the query.
     *
     * @param string $fieldName Field name
     * @param array  $args      Field arguments
     *
     * @return $this
     */
    public function addField(string $fieldName, array $args): self
    {
        return $this->setParam($fieldName, $args);
    }
}
