<?php

namespace Elastica\Processor;

use Elastica\Param;
use Elastica\Util;

/**
 * Elastica Processor object.
 *
 * @author Federico Panini <fpanini@gmail.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/ingest-processors.html
 */
abstract class AbstractProcessor extends Param
{
    protected function _getBaseName()
    {
        $shortName = (new \ReflectionClass($this))->getShortName();
        $shortName = \preg_replace('/Processor$/', '', $shortName);

        return Util::toSnakeCase($shortName);
    }
}
