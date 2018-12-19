<?php

namespace Elastica\Processor;

/**
 * Elastica Json Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/json-processor.html
 */
class Json extends AbstractProcessor
{
    const DEFAULT_TARGET_FIELD_VALUE = 'field';
    const DEFAULT_ADD_TO_ROOT_VALUE = false;

    /**
     * Json constructor.
     *
     * @param string $field
     */
    public function __construct(string $field)
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
    public function setField(string $field): self
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
    public function setTargetField(string $targetField): self
    {
        return $this->setParam('target_field', $targetField);
    }

    /**
     * Set add_to_root. Default value false.
     *
     * @param bool $addToRoot
     *
     * @return $this
     */
    public function setAddToRoot(bool $addToRoot): self
    {
        return $this->setParam('add_to_root', $addToRoot);
    }
}
