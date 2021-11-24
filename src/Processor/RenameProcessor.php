<?php

namespace Elastica\Processor;

/**
 * Elastica Rename Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/rename-processor.html
 */
class RenameProcessor extends AbstractProcessor
{
    use Traits\IgnoreFailureTrait;
    use Traits\IgnoreMissingTrait;
    use Traits\TargetFieldTrait;

    public const DEFAULT_IGNORE_MISSING_VALUE = false;

    public function __construct(string $field, string $targetField)
    {
        $this->setField($field);
        $this->setTargetField($targetField);
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
