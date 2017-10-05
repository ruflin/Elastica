<?php
namespace Elastica\Processor;

/**
 * Elastica Remove Processor.
 *
 * @author   Federico Panini <fpanini@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/remove-processor.html
 */
class Remove extends AbstractProcessor
{
    /**
     * Remove constructor.
     *
     * @param string|array $field
     */
    public function __construct($field)
    {
        $this->setField($field);
    }

    /**
     * Set field.
     *
     * @param string|array $field
     *
     * @return $this
     */
    public function setField($field)
    {
        return $this->setParam('field', $field);
    }
}
