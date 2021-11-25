<?php

namespace Elastica\Processor;

/**
 * Elastica Join Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/join-processor.html
 */
class JoinProcessor extends AbstractProcessor
{
    use Traits\FieldTrait;
    use Traits\IgnoreFailureTrait;

    public function __construct(string $field, string $separator)
    {
        $this->setField($field);
        $this->setSeparator($separator);
    }

    /**
     * Set the separator.
     *
     * @return $this
     */
    public function setSeparator(string $separator): self
    {
        return $this->setParam('separator', $separator);
    }
}
