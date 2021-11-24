<?php

namespace Elastica\Processor;

/**
 * Elastica Convert Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/convert-processor.html
 */
class ConvertProcessor extends AbstractProcessor
{
    use Traits\FieldTrait;
    use Traits\IgnoreFailureTrait;
    use Traits\IgnoreMissingTrait;
    use Traits\TargetFieldTrait;

    public const DEFAULT_TARGET_FIELD_VALUE = 'field';
    public const DEFAULT_IGNORE_MISSING_VALUE = false;

    public function __construct(string $field, string $type)
    {
        $this->setField($field);
        $this->setType($type);
    }

    /**
     * Set field value.
     *
     * @return $this
     */
    public function setType(string $type): self
    {
        return $this->setParam('type', $type);
    }
}
