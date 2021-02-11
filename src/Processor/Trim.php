<?php

namespace Elastica\Processor;

trigger_deprecation('ruflin/elastica', '7.1.0', 'The "%s" class is deprecated, use "%s" instead. It will be removed in 8.0.', Trim::class, TrimProcessor::class);

/**
 * Elastica Trim Processor.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/trim-processor.html
 * @deprecated since version 7.1.0, use the TrimProcessor class instead.
 */
class Trim extends TrimProcessor
{
}
