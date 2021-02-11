<?php

namespace Elastica\Processor;

/**
 * Elastica Sort Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/sort-processor.html
 */
class SortProcessor extends AbstractProcessor
{
    public const DEFAULT_ORDER_VALUE = 'asc';

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
