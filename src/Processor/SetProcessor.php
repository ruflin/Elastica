<?php

namespace Elastica\Processor;

/**
 * Elastica Set Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/set-processor.html
 */
class SetProcessor extends AbstractProcessor
{
    use Traits\FieldTrait;
    use Traits\IgnoreFailureTrait;

    public const DEFAULT_OVERRIDE_VALUE = true;

    public function __construct(string $field, string $value)
    {
        $this->setField($field);
        $this->setValue($value);
    }

    /**
     * Set field value.
     *
     * @return $this
     */
    public function setValue(string $value): self
    {
        return $this->setParam('value', $value);
    }

    /**
     * Set override. Default true.
     *
     * @return $this
     */
    public function setOverride(bool $override): self
    {
        return $this->setParam('override', $override);
    }
}
