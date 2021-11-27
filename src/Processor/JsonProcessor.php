<?php

namespace Elastica\Processor;

/**
 * Elastica Json Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/json-processor.html
 */
class JsonProcessor extends AbstractProcessor
{
    use Traits\FieldTrait;
    use Traits\IgnoreFailureTrait;
    use Traits\TargetFieldTrait;

    public const DEFAULT_TARGET_FIELD_VALUE = 'field';
    public const DEFAULT_ADD_TO_ROOT_VALUE = false;

    public function __construct(string $field)
    {
        $this->setField($field);
    }

    /**
     * Set add_to_root. Default value false.
     *
     * @return $this
     */
    public function setAddToRoot(bool $addToRoot): self
    {
        return $this->setParam('add_to_root', $addToRoot);
    }
}
