<?php
namespace Elastica\Processor;

/**
 * Elastica Trim Processor.
 *
 * @author   Federico Panini <fpanini@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/trim-processor.html
 */
class Trim extends AbstractProcessor
{
    /**
     * Sort constructor.
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
}
