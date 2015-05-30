<?php
namespace Elastica\Filter;

/**
 * Exists query.
 *
 * @author Oleg Cherniy <oleg.cherniy@gmail.com>
 *
 * @link http://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-filter.html
 */
class Exists extends AbstractFilter
{
    /**
     * Construct exists filter.
     *
     * @param string $field
     */
    public function __construct($field)
    {
        $this->setField($field);
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
        return $this->setParam('field', $field);
    }
}
