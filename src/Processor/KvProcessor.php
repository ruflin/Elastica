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
    use Traits\FieldTrait;
    use Traits\IgnoreFailureTrait;
    use Traits\IgnoreMissingTrait;
    use Traits\TargetFieldTrait;

    public const DEFAULT_TARGET_FIELD_VALUE = null;
    public const DEFAULT_IGNORE_MISSING_VALUE = false;

    public function __construct(string $field, string $fieldSplit, string $valueSplit)
    {
        $this->setField($field);
        $this->setFieldSplit($fieldSplit);
        $this->setValueSplit($valueSplit);
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
}
