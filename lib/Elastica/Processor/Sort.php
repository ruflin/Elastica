<?php
namespace Elastica\Processor;

/**
 * Elastica Sort Processor.
 *
 * @author   Federico Panini <fpanini@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/sort-processor.html
 */
class Sort extends AbstractProcessor
{
    /**
     * Split constructor.
     *
     * @param $field
     */
    public function __construct($field)
    {
        $this->setField($field);
    }

    /**
     * Set the field.
     *
     * @param string $field
     *
     * @return $this
     */
    public function setField(string $field)
    {
        return $this->setParam('field', $field);
    }

    /**
     * Set order. Default 'asc'.
     *
     * @param string $order
     *
     * @return $this
     */
    public function setOrder(string $order)
    {
        return $this->setParam('order', $order);
    }
}
