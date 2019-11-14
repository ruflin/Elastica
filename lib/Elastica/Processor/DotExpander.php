<?php

namespace Elastica\Processor;

/**
 * Elastica DotExpander Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/dot-expand-processor.html
 */
class DotExpander extends AbstractProcessor
{
    /**
     * DotExpander constructor.
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
}
