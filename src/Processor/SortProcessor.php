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
    use Traits\FieldTrait;
    use Traits\IgnoreFailureTrait;

    public const DEFAULT_ORDER_VALUE = 'asc';

    public function __construct(string $field)
    {
        $this->setField($field);
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
