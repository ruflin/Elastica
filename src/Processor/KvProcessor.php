<?php

namespace Elastica\Processor;

/**
 * Elastica KV Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/kv-processor.html
 */
class KvProcessor extends AbstractProcessor
{
    public const DEFAULT_TARGET_FIELD_VALUE = null;
    public const DEFAULT_IGNORE_MISSING_VALUE = false;

    public function __construct(string $field, string $fieldSplit, string $valueSplit)
    {
        $this->setField($field);
        $this->setFieldSplit($fieldSplit);
        $this->setValueSplit($valueSplit);
    }

    /**
     * Set field name.
     *
     * @return $this
     */
    public function setField(string $field): self
    {
        return $this->setParam('field', $field);
    }

    /**
     * Set field_split.
     *
     * @return $this
     */
    public function setFieldSplit(string $fieldSplit): self
    {
        return $this->setParam('field_split', $fieldSplit);
    }

    /**
     * Set value_split.
     *
     * @return $this
     */
    public function setValueSplit(string $valueSplit): self
    {
        return $this->setParam('value_split', $valueSplit);
    }

    /**
     * Set target_field. Default value null.
     *
     * @return $this
     */
    public function setTargetField(string $targetField): self
    {
        return $this->setParam('target_field', $targetField);
    }

    /**
     * Set include_keys.
     *
     * @return $this
     */
    public function setIncludeKeys(array $listOfKeys): self
    {
        return $this->setParam('include_keys', $listOfKeys);
    }

    /**
     * Set exclude_keys.
     *
     * @return $this
     */
    public function setExcludeKeys(array $listOfKeys): self
    {
        return $this->setParam('exclude_keys', $listOfKeys);
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
