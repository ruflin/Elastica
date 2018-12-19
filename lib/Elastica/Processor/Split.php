<?php

namespace Elastica\Processor;

/**
 * Elastica Split Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/split-processor.html
 */
class Split extends AbstractProcessor
{
    const DEFAULT_IGNORE_MISSING_VALUE = false;

    /**
     * Split constructor.
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
