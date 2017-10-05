<?php
namespace Elastica\Processor;

/**
 * Elastica Json Processor.
 *
 * @author   Federico Panini <fpanini@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/json-processor.html
 */
class Json extends AbstractProcessor
{
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
     * Set target_field. Default field.
     *
     * @param string $targetField
     *
     * @return $this
     */
    public function setTargetField(string $targetField)
    {
        return $this->setParam('target_field', $targetField);
    }

    /**
     * Set add_to_root. Default false.
     *
     * @param bool $addToRoot
     *
     * @return $this
     */
    public function setAddToRoot(bool $addToRoot)
    {
        return $this->setParam('add_to_root', $addToRoot);
    }
}
