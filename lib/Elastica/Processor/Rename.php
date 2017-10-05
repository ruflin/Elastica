<?php
namespace Elastica\Processor;

/**
 * Elastica Rename Processor.
 *
 * @author   Federico Panini <fpanini@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/rename-processor.html
 */
class Rename extends AbstractProcessor
{
    /**
     * Rename constructor.
     *
     * @param string $field
     * @param string $targetField
     */
    public function __construct(string $field, string $targetField)
    {
        $this->setField($field);
        $this->setTargetField($targetField);
    }

    /**
     * Set field.
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
     * Set target_field.
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
