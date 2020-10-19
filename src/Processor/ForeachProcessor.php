<?php

namespace Elastica\Processor;

/**
 * Elastica Attachment Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 * @author Thibaut Simon-Fine <tsimonfine@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/foreach-processor.html
 */
class ForeachProcessor extends AbstractProcessor
{
    public const DEFAULT_IGNORE_MISSING_VALUE = false;

    /**
     * Foreach constructor.
     */
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

    /**
     * Set processor.
     *
     * @param AbstractProcessor
     *
     * @return $this
     */
    public function setProcessor(AbstractProcessor $processor): self
    {
        return $this->setParam('processor', [$processor]);
    }

    /**
     * Set raw processor.
     * Example : ['remove' => ['field' => 'user_agent']].
     *
     * @return $this
     */
    public function setRawProcessor(array $processor): self
    {
        return $this->setParam('processor', $processor);
    }

    /**
     * Set ignore_missing. Default value false.
     *
     * If true and field does not exist or is null, the processor quietly exits without modifying the document
     *
     * @return $this
     */
    public function setIgnoreMissing(bool $ignoreMissing): self
    {
        return $this->setParam('ignore_missing', $ignoreMissing);
    }
}
