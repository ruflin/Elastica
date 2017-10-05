<?php
namespace Elastica\Processor;

/**
 * Elastica Set Processor.
 *
 * @author   Federico Panini <fpanini@gmail.com>
 *
 * @link https://www.elastic.co/guide/en/elasticsearch/reference/current/ingest-processors.html
 */
class Set extends AbstractProcessor
{
    /**
     * Set constructor.
     *
     * @param string $field field name
     * @param string $value field value
     */
    public function __construct(string $field, string $value)
    {
        $this->setField($field);
        $this->setValue($value);
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
     * @param string $value
     *
     * @return $this
     */
    public function setValue(string $value)
    {
        return $this->setParam('value', $value);
    }

    /**
     * Set override. Default true.
     *
     * @param bool $override
     *
     * @return $this
     */
    public function setOverride(bool $override)
    {
        return $this->setParam('override', $override);
    }
}
