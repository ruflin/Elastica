<?php

namespace Elastica\Processor;

/**
 * Elastica Lowercase Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/lowercase-processor.html
 */
class LowercaseProcessor extends AbstractProcessor
{
    use Traits\FieldTrait;
    use Traits\IgnoreFailureTrait;
    use Traits\IgnoreMissingTrait;

    public function __construct(string $field)
    {
        $this->setField($field);
    }
}
