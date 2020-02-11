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
    public const DEFAULT_TARGET_FIELD_VALUE = 'field';
    public const DEFAULT_ADD_TO_ROOT_VALUE = false;

    /**
     * Json constructor.
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
     * Set target_field. Default field.
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
     * @return $this
     */
    public function setAddToRoot(bool $addToRoot): self
    {
        return $this->setParam('add_to_root', $addToRoot);
    }
}
