<?php

namespace Elastica\Processor;

/**
 * Elastica Append Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/append-processor.html
 */
class AppendProcessor extends AbstractProcessor
{
    use Traits\FieldTrait;
    use Traits\IgnoreFailureTrait;

    /**
     * @param string       $field field name
     * @param array|string $value field values to append
     */
    public function __construct(string $field, $value)
    {
        $this->setField($field);
        $this->setValue($value);
    }

    /**
     * Set field value.
     *
     * @param array|string $value
     *
     * @return $this
     */
    public function setValue($value): self
    {
        return $this->setParam('value', $value);
    }

    /**
     * Set allow_duplicates value.
     *
     * @return $this
     */
    public function setAllowDuplicates(bool $allowDuplicates): self
    {
        return $this->setParam('allow_duplicates', $allowDuplicates);
    }
}
