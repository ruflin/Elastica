<?php

namespace Elastica\Processor;

/**
 * Elastica Sort Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/sort-processor.html
 */
class Sort extends AbstractProcessor
{
    public const DEFAULT_ORDER_VALUE = 'asc';

    /**
     * Sort constructor.
     *
     * @param $field
     */
    public function __construct(string $field)
    {
        $this->setField($field);
    }

    /**
     * Set the field.
     *
     * @return $this
     */
    public function setField(string $field): self
    {
        return $this->setParam('field', $field);
    }

    /**
     * Set order. Default 'asc'.
     *
     * @return $this
     */
    public function setOrder(string $order): self
    {
        return $this->setParam('order', $order);
    }
}
