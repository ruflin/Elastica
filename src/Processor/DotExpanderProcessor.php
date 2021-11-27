<?php

namespace Elastica\Processor;

/**
 * Elastica DotExpander Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/dot-expand-processor.html
 */
class DotExpanderProcessor extends AbstractProcessor
{
    use Traits\FieldTrait;
    use Traits\IgnoreFailureTrait;

    public function __construct(string $field)
    {
        $this->setField($field);
    }
}
