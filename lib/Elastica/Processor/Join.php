<?php

namespace Elastica\Processor;

/**
 * Elastica Join Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/join-processor.html
 */
class Join extends AbstractProcessor
{
    /**
     * Join constructor.
     *
     * @param string $field
     * @param string $separator
     */
    public function __construct(string $field, string $separator)
    {
        $this->setField($field);
        $this->setSeparator($separator);
    }

    /**
     * Set the field.
     *
     * @param string $field
     *
     * @return $this
     */
    public function setField(string $field): self
    {
        return $this->setParam('field', $field);
    }

    /**
     * Set the separator.
     *
     * @param string $separator
     *
     * @return $this
     */
    public function setSeparator(string $separator): self
    {
        return $this->setParam('separator', $separator);
    }
}
