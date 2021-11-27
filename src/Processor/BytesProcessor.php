<?php

namespace Elastica\Processor;

/**
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/bytes-processor.html
 */
class BytesProcessor extends AbstractProcessor
{
    use Traits\FieldTrait;
    use Traits\IgnoreFailureTrait;
    use Traits\IgnoreMissingTrait;
    use Traits\TargetFieldTrait;

    public function __construct(string $field)
    {
        $this->setField($field);
    }
}
