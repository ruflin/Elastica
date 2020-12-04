<?php

namespace Elastica\Processor;

/**
 * Elastica Uppercase Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/uppercase-processor.html
 */
class UppercaseProcessor extends AbstractProcessor
{
    public function __construct(string $field)
    {
        $this->setField($field);
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
}
