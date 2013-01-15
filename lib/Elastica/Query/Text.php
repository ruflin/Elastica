<?php

namespace Elastica\Query;

/**
 * Text query
 *
 * @category Xodoa
 * @package Elastica
 * @author Nicolas Ruflin <spam@ruflin.com>
 * @link http://www.elasticsearch.org/guide/reference/query-dsl/text-query.html
 */
class Text extends AbstractQuery
{
    /**
     * Sets a param for the message array
     *
     * @param  string                   $field
     * @param  mixed                    $values
     * @return \Elastica\Query\Text
     */
    public function setField($field, $values)
    {
        return $this->setParam($field, $values);
    }

    /**
     * Sets a param for the given field
     *
     * @param  string                   $field
     * @param  string                   $key
     * @param  string                   $value
     * @return \Elastica\Query\Text
     */
    public function setFieldParam($field, $key, $value)
    {
        if (!isset($this->_params[$field])) {
            $this->_params[$field] = array();
        }

        $this->_params[$field][$key] = $value;

        return $this;
    }

    /**
     * Sets the query string
     *
     * @param  string                   $field
     * @param  string                   $query
     * @return \Elastica\Query\Text
     */
    public function setFieldQuery($field, $query)
    {
        return $this->setFieldParam($field, 'query', $query);
    }

    /**
     * Set field type
     *
     * @param  string                   $field
     * @param  string                   $type  Text query type
     * @return \Elastica\Query\Text
     */
    public function setFieldType($field, $type)
    {
        return $this->setFieldParam($field, 'type', $type);
    }

    /**
     * Set field max expansions
     *
     * @param  string                   $field
     * @param  int                      $maxExpansions
     * @return \Elastica\Query\Text
     */
    public function setFieldMaxExpansions($field, $maxExpansions)
    {
        return $this->setFieldParam($field, 'max_expansions', $maxExpansions);
    }
}
