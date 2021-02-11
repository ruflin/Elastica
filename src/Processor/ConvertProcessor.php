<?php

namespace Elastica\Processor;

/**
 * Elastica Convert Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/convert-processor.html
 */
class ConvertProcessor extends AbstractProcessor
{
    public const DEFAULT_TARGET_FIELD_VALUE = 'field';
    public const DEFAULT_IGNORE_MISSING_VALUE = false;

    public function __construct(string $field, string $type)
    {
        $this->setField($field);
        $this->setType($type);
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
     * @return $this
     */
    public function setType(string $type): self
    {
        return $this->setParam('type', $type);
    }

    /**
     * Set target_field. Default value field.
     *
     * @return $this
     */
    public function setTargetField(string $targetField): self
    {
        return $this->setParam('target_field', $targetField);
    }

    /**
     * Set ignore_missing. Default value false.
     *
     * @param bool $ignoreMissing only these values are allowed (integer|float|string|boolean|auto)
     *
     * @return $this
     */
    public function setIgnoreMissing(bool $ignoreMissing): self
    {
        return $this->setParam('ignore_missing', $ignoreMissing);
    }
}
