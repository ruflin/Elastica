<?php
namespace Elastica\Processor;

/**
 * Elastica Convert Processor.
 *
 * @author   Federico Panini <fpanini@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/append-processor.html
 */
class Convert extends AbstractProcessor
{
    public function __construct($field, $type)
    {
        $this->setField($field);
        $this->setType($type);
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
     * Set field value.
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type)
    {
        return $this->setParam('type', $type);
    }

    /**
     * Set target_field. Default value field.
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
