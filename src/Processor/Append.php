<?php

namespace Elastica\Processor;

/**
 * Elastica Append Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/append-processor.html
 */
class Append extends AbstractProcessor
{
    /**
     * Append constructor.
     *
     * @param string       $field field name
     * @param array|string $value field values to append
     */
    public function __construct(string $field, $value)
    {
        $this->setField($field);
        $this->setValue($value);
    }

    /**
     * Set field.
     *
     * @return $this
     */
    public function setField(string $field): self
    {
        return $this->setParam('field', $field);
    }

    /**
     * Set field value.
     *
     * @param array|string $value
     *
     * @return $this
     */
    public function setValue($value): self
    {
        return $this->setParam('value', $value);
    }
}
