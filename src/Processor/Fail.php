<?php

namespace Elastica\Processor;

trigger_deprecation('ruflin/elastica', '7.1.0', 'The "%s" class is deprecated, use "%s" instead. It will be removed in 8.0.', Fail::class, FailProcessor::class);

/**
 * Elastica Fail Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/fail-processor.html
 * @deprecated since version 7.1.0, use the FailProcessor class instead.
 */
class Fail extends FailProcessor
{
}
