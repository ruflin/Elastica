<?php
namespace Elastica\Query;

/**
 * Exists query.
 *
 * @author Oleg Cherniy <oleg.cherniy@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-exists-query.html
 */
class Exists extends AbstractQuery
{
    /**
     * Construct exists query.
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
