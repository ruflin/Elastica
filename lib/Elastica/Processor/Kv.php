<?php
namespace Elastica\Processor;

/**
 * Elastica KV Processor.
 *
 * @author   Federico Panini <fpanini@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/kv-processor.html
 */
class Kv extends AbstractProcessor
{
    /**
     * Kv constructor.
     *
     * @param string $field
     * @param string $fieldSplit
     * @param string $valueSplit
     */
    public function __construct(string $field, string $fieldSplit, string $valueSplit)
    {
        $this->setField($field);
        $this->setFieldSplit($fieldSplit);
        $this->setValueSplit($valueSplit);
    }

    /**
     * Set field name.
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
     * Set field_split.
     *
     * @param string $fieldSplit
     *
     * @return $this
     */
    public function setFieldSplit(string $fieldSplit)
    {
        return $this->setParam('field_split', $fieldSplit);
    }

    /**
     * Set value_split.
     *
     * @param string $valueSplit
     *
     * @return $this
     */
    public function setValueSplit(string $valueSplit)
    {
        return $this->setParam('value_split', $valueSplit);
    }

    /**
     * Set target_field. Default value @timestamp.
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
     * Set include_keys.
     *
     * @param array $listOfKeys
     *
     * @return $this
     */
    public function setIncludeKeys(array $listOfKeys)
    {
        return $this->setParam('include_keys', $listOfKeys);
    }

    /**
     * Set exclude_keys.
     *
     * @param array $listOfKeys
     *
     * @return $this
     */
    public function setExcludeKeys(array $listOfKeys)
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
    public function setIgnoreMissing(bool $ignoreMissing)
    {
        return $this->setParam('ignore_missing', $ignoreMissing);
    }
}
