<?php

namespace Elastica\Query;

use Elastica\Param;
use Elastica\Util;

/**
 * Abstract query object. Should be extended by all query types.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 */
abstract class AbstractQuery extends Param
{
    protected function _getBaseName()
    {
        $shortName = (new \ReflectionClass($this))->getShortName();
        $shortName = \preg_replace('/Query$/', '', $shortName);

        return Util::toSnakeCase($shortName);
    }
}
