<?php

namespace Elastica\Query;

/**
 * Missing Query.
 *
 * @author Maciej Wiercinski <maciej@wiercinski.net>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-missing-query.html
 */
class Missing extends AbstractQuery
{
    /**
     * Construct missing query.
     *
     * @param string $field OPTIONAL
     */
    public function __construct($field = '')
    {
        if (strlen($field)) {
            $this->setField($field);
        }
    }

    /**
     * Set field.
     *
     * @param string $field
     *
     * @return $this
     */
    public function setField($field)
    {
        return $this->setParam('field', (string) $field);
    }

    /**
     * Set "existence" parameter.
     *
     * @param bool $existence
     *
     * @return $this
     */
    public function setExistence($existence)
    {
        return $this->setParam('existence', (bool) $existence);
    }

    /**
     * Set "null_value" parameter.
     *
     * @param bool $nullValue
     *
     * @return $this
     */
    public function setNullValue($nullValue)
    {
        return $this->setParam('null_value', (bool) $nullValue);
    }
}
